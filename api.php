<?php
// Retour JSON propre — bufferise la sortie et désactive l'affichage des erreurs
ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';
$result = '';

if ($action === 'choose') {
    $cmd = strtolower(trim($_POST['command'] ?? ''));
    $result = handleCommandChoice($cmd);
} elseif ($action === 'param') {
    $cmd = $_POST['command'] ?? '';
    $value = $_POST['value'] ?? '';
    $data = json_decode($_POST['data'] ?? '{}', true);
    $result = handleParameter($cmd, $value, $data);
} else {
    $result = 'Action non reconnue.';
}

// Nettoyer tout contenu envoyé accidentellement et renvoyer JSON
$payload = json_encode(['result' => $result], JSON_UNESCAPED_UNICODE);
if ($payload === false) {
     $payload = json_encode(['result' => 'Erreur interne: impossible d encoder le résultat.']);
}
// Supprime le buffer pour éviter tout texte non-JSON (warnings, BOM...)
if (ob_get_length()) ob_clean();
echo $payload;
exit;


function handleCommandChoice($cmd) {
   $commands = ['gain', 'directivite', 'efficacite', 'attenuation', 'capacite', 'budget_optique', 'help', 'clear'];
   if (!in_array($cmd, $commands)) {
       return "Commande inconnue. Tapez 'help' pour la liste.";
   }
   if ($cmd === 'help') {
       return "Commandes disponibles:\n" .
           "  gain        : Calcul du gain \n" .
           "  directivite : Calcul de la Directivité \n" .
           "  efficacite  : Calcul de l'Efficacité \n" .
           "  attenuation : Calcul de l'atténuation sur une ligne de transmission\n" .
           "  capacite    : Calcul de la capacité d'un canal de communication (formule de Shannon)\n" .
           "  budget_optique: Calcul de la marge sur une liaison optique \n" .
           "  clear       : Effacer l'écran\n" .
           "  help        : Cette aide";
   }
   if ($cmd === 'clear') {
       return '__CLEAR__';
   }
   return askNextParameter($cmd, []);
}


function askNextParameter($cmd, $data) {
   if ($cmd === 'gain') {
       $paramIndex = count($data);
       if ($paramIndex == 0) {
           return "__ASK__|" . json_encode($data) . "|Choisissez la méthode de calcul du gain:\n1 - Par puissances (Pout, Pin)\n2 - Par efficacité et directivité (η, D)";
       }
       if ($paramIndex == 1) {
           $type = $data[0];
           if ($type == 1) {
               return "__ASK__|" . json_encode($data) . "|Puissance de sortie (W) ?";
           } elseif ($type == 2) {
               return "__ASK__|" . json_encode($data) . "|Efficacité η (entre 0 et 1) ?";
           } else {
               return "Erreur : type invalide (1 ou 2).";
           }
       }
       if ($paramIndex == 2) {
           $type = $data[0];
           if ($type == 1) {
               return "__ASK__|" . json_encode($data) . "|Puissance d'entrée (W) ?";
           } elseif ($type == 2) {
               return "__ASK__|" . json_encode($data) . "|Directivité D (linéaire) ?";
           }
       }
       return computeGain($data);
   }
   elseif ($cmd === 'directivite') {
       $questions = [
           'Gain (en dB) ?',
           'Efficacité η (entre 0 et 1) ?'
       ];
       $paramIndex = count($data);
       if ($paramIndex < count($questions)) {
           return "__ASK__|" . json_encode($data) . "|" . $questions[$paramIndex];
       } else {
           return computeDirectivite($data);
       }
   }
   elseif ($cmd === 'efficacite') {
       $paramIndex = count($data);
       if ($paramIndex == 0) {
           return "__ASK__|" . json_encode($data) . "|Choisissez la méthode de calcul de l'efficacité:\n1 - Par puissances (Pray, Pin)\n2 - Par gain et directivité (G_dB, D_dB)";
       }
       if ($paramIndex == 1) {
           $type = $data[0];
           if ($type == 1) {
               return "__ASK__|" . json_encode($data) . "|Puissance rayonnée (W) ?";
           } elseif ($type == 2) {
               return "__ASK__|" . json_encode($data) . "|Gain (en dB) ?";
           } else {
               return "Erreur : type invalide (1 ou 2).";
           }
       }
       if ($paramIndex == 2) {
           $type = $data[0];
           if ($type == 1) {
               return "__ASK__|" . json_encode($data) . "|Puissance d'entrée (W) ?";
           } elseif ($type == 2) {
               return "__ASK__|" . json_encode($data) . "|Directivité (en dB) ?";
           }
       }
       return computeEfficacite($data);
   }
   elseif ($cmd === 'attenuation') {
       $paramIndex = count($data);
       if ($paramIndex == 0) {
           return "__ASK__|" . json_encode($data) . "|Choisissez la méthode de calcul de l'atténuation:\n1 - Par coefficient d'atténuation et longueur\n2 - Par puissances d'entrée et sortie";
       }
       if ($paramIndex == 1) {
           $type = $data[0];
           if ($type == 1) {
               return "__ASK__|" . json_encode($data) . "|Coefficient d'atténuation (dB par unité de longueur) ?";
           } elseif ($type == 2) {
               return "__ASK__|" . json_encode($data) . "|Puissance d'entrée (W) ?";
           } else {
               return "Erreur : type invalide (1 ou 2).";
           }
       }
       if ($paramIndex == 2) {
           $type = $data[0];
           if ($type == 1) {
               return "__ASK__|" . json_encode($data) . "|Longueur (en unités correspondantes) ?";
           } elseif ($type == 2) {
               return "__ASK__|" . json_encode($data) . "|Puissance de sortie (W) ?";
           }
       }
       return computeAttenuation($data);
   }
   elseif ($cmd === 'capacite') {
       $paramIndex = count($data);
       if ($paramIndex == 0) {
           return "__ASK__|" . json_encode($data) . "|Choisissez le type de calcul:\n1 - Capacité totale (avec N canaux)\n2 - Nombre de canaux (à partir de la capacité totale)";
       }
       if ($paramIndex == 1) {
           $type = $data[0];
           if ($type == 1) {
               return "__ASK__|" . json_encode($data) . "|Nombre de canaux N ?";
           } elseif ($type == 2) {
               return "__ASK__|" . json_encode($data) . "|Capacité totale C (en bps) ?";
           } else {
               return "Erreur : type invalide (1 ou 2).";
           }
       }
       if ($paramIndex == 2) {
           $type = $data[0];
           if ($type == 1 || $type == 2) {
               return "__ASK__|" . json_encode($data) . "|Bande passante par canal B (en Hz) ?";
           }
       }
       if ($paramIndex == 3) {
           $type = $data[0];
           if ($type == 1 || $type == 2) {
               return "__ASK__|" . json_encode($data) . "|Rapport signal sur bruit SNR (en linéaire, pas en dB) ?";
           }
       }
       return computeCapacite($data);
   }
   elseif ($cmd === 'budget_optique') {
       $paramIndex = count($data);
       $questions = [
           "Distance (en km) ?",
           "Atténuation de la fibre (en dB/km) ?",
           "Nombre de connecteurs ?",
           "Perte par connecteur (en dB) ?",
           "Nombre d'épissures ?",
           "Perte par épissure (en dB) ?",
           "Puissance émise (en dBm) ?",
           "Sensibilité du récepteur (en dBm) ?"
       ];
       if ($paramIndex < count($questions)) {
           return "__ASK__|" . json_encode($data) . "|" . $questions[$paramIndex];
       } else {
           return computeBudgetOptique($data);
       }
   }
   else {
       return "Erreur interne.";
   }
}


function handleParameter($cmd, $value, $data) {
   if (!is_numeric($value)) {
       return "Erreur : la valeur doit être numérique.";
   }
   $data[] = $value;
   return askNextParameter($cmd, $data);
}


function computeGain($params) {
   $type = $params[0];
   if ($type == 1) {
       if (count($params) < 3) return "__RESULT__|Erreur : paramètres manquants.";
       $pout = $params[1];
       $pin = $params[2];
       if ($pin <= 0) return "__RESULT__|Erreur : puissance d'entrée doit être > 0.";
       $gain = 10 * log10($pout / $pin);
       return "__RESULT__|Gain = " . round($gain, 2) . " dB";
   } elseif ($type == 2) {
       if (count($params) < 3) return "__RESULT__|Erreur : paramètres manquants.";
       $eta = $params[1];
       $d = $params[2];
       if ($eta <= 0) return "__RESULT__|Erreur : l'efficacité doit être > 0.";
       if ($d <= 0) return "__RESULT__|Erreur : la directivité doit être > 0.";
       $g_lin = $eta * $d;
       $g_dB = 10 * log10($g_lin);
       return "__RESULT__|Gain = " . round($g_dB, 2) . " dB (à partir de η = $eta et D = $d)";
   } else {
       return "__RESULT__|Erreur : type inconnu.";
   }
}


function computeDirectivite($params) {
   if (count($params) < 2) return "__RESULT__|Erreur : paramètres manquants.";
   $gdB = $params[0];
   $eta = $params[1];
   if ($eta <= 0) return "__RESULT__|Erreur : efficacité doit être > 0.";
   $d_dB = $gdB - 10 * log10($eta);
   return "__RESULT__|Directivité = " . round($d_dB, 2) . " dB";
}


function computeEfficacite($params) {
   $type = $params[0];
   if ($type == 1) {
       if (count($params) < 3) return "__RESULT__|Erreur : paramètres manquants.";
       $pray = $params[1];
       $pin = $params[2];
       if ($pin <= 0) return "__RESULT__|Erreur : puissance d'entrée doit être > 0.";
       $eta = $pray / $pin;
       return "__RESULT__|Efficacité η = " . round($eta, 4) . " (soit " . round($eta*100, 2) . " %)";
   } elseif ($type == 2) {
       if (count($params) < 3) return "__RESULT__|Erreur : paramètres manquants.";
       $gdB = $params[1];
       $ddB = $params[2];
       $g_lin = pow(10, $gdB/10);
       $d_lin = pow(10, $ddB/10);
       $eta = $g_lin / $d_lin;
       return "__RESULT__|Efficacité η = " . round($eta, 4) . " (soit " . round($eta*100, 2) . " %)";
   } else {
       return "__RESULT__|Erreur : type inconnu.";
   }
}


function computeAttenuation($params) {
   $type = $params[0];
   if ($type == 1) {
       if (count($params) < 3) return "__RESULT__|Erreur : paramètres manquants.";
       $alpha = $params[1];
       $length = $params[2];
       if ($alpha < 0) return "__RESULT__|Erreur : le coefficient d'atténuation ne peut pas être négatif.";
       if ($length < 0) return "__RESULT__|Erreur : la longueur ne peut pas être négative.";
       $attenuation = $alpha * $length;
       return "__RESULT__|Atténuation = " . round($attenuation, 2) . " dB";
   } elseif ($type == 2) {
       if (count($params) < 3) return "__RESULT__|Erreur : paramètres manquants.";
       $pin = $params[1];
       $pout = $params[2];
       if ($pin <= 0) return "__RESULT__|Erreur : la puissance d'entrée doit être > 0.";
       if ($pout <= 0) return "__RESULT__|Erreur : la puissance de sortie doit être > 0.";
       if ($pout > $pin) return "__RESULT__|Erreur : la puissance de sortie ne peut pas être supérieure à l'entrée (atténuation négative).";
       $attenuation = 10 * log10($pin / $pout);
       return "__RESULT__|Atténuation = " . round($attenuation, 2) . " dB";
   } else {
       return "__RESULT__|Erreur : type inconnu.";
   }
}


function computeCapacite($params) {
   $type = $params[0];
   if ($type == 1) {
       // Capacité totale avec N canaux
       if (count($params) < 4) return "__RESULT__|Erreur : paramètres manquants.";
       $N = $params[1];
       $B = $params[2];
       $SNR = $params[3];
       if ($N <= 0 || $B <= 0 || $SNR <= 0) {
           return "__RESULT__|Erreur : tous les paramètres doivent être positifs.";
       }
    $C_par_canal = $B * (log(1 + $SNR) / log(2));
       $C_total = $N * $C_par_canal;
       return "__RESULT__|Capacité par canal = " . round($C_par_canal, 2) . " bps\nCapacité totale = " . round($C_total, 2) . " bps";
   } elseif ($type == 2) {
       // Nombre de canaux N à partir de la capacité totale
       if (count($params) < 4) return "__RESULT__|Erreur : paramètres manquants.";
       $C_total = $params[1];
       $B = $params[2];
       $SNR = $params[3];
       if ($C_total <= 0 || $B <= 0 || $SNR <= 0) {
           return "__RESULT__|Erreur : tous les paramètres doivent être positifs.";
       }
    $C_par_canal = $B * (log(1 + $SNR) / log(2));
       if ($C_par_canal <= 0) return "__RESULT__|Erreur : capacité par canal nulle.";
       $N = $C_total / $C_par_canal;
       return "__RESULT__|Capacité par canal = " . round($C_par_canal, 2) . " bps\nNombre de canaux N = " . round($N, 2) . " (arrondi)";
   } else {
       return "__RESULT__|Erreur : type inconnu.";
   }
}


function computeBudgetOptique($params) {
   if (count($params) < 8) return "__RESULT__|Erreur : paramètres manquants.";
   $distance = $params[0];
   $att_fibre = $params[1];
   $nb_conn = $params[2];
   $perte_conn = $params[3];
   $nb_episs = $params[4];
   $perte_episs = $params[5];
   $p_emise = $params[6];
   $sensibilite = $params[7];
}


   if ($distance < 0 || $att_fibre < 0 || $nb_conn < 0 || $perte_conn < 0 || $nb_episs < 0 || $perte_episs < 0) {
       return "__RESULT__|Erreur : les valeurs négatives ne sont pas autorisées.";
   }


   $perte_fibre = $att_fibre * $distance;
   $perte_connecteurs = $nb_conn * $perte_conn;
   $perte_epissures = $nb_episs * $perte_episs;
   $perte_totale = $perte_fibre + $perte_connecteurs + $perte_epissures;


   $p_recue = $p_emise - $perte_totale;
   $marge = $p_recue - $sensibilite;


   $resultat = "Perte fibre: " . round($perte_fibre, 2) . " dB\n";
   $resultat .= "Perte connecteurs: " . round($perte_connecteurs, 2) . " dB\n";
   $resultat .= "Perte épissures: " . round($perte_epissures, 2) . " dB\n";
   $resultat .= "Perte totale: " . round($perte_totale, 2) . " dB\n";
   $resultat .= "Puissance reçue: " . round($p_recue, 2) . " dBm\n";
   $resultat .= "Marge: " . round($marge, 2) . " dB\n";


   if ($p_recue > $sensibilite) {
       $resultat .= "=> Liaison possible (P_reçue > sensibilité)";
   } else {
       $resultat .= "=> Liaison impossible (P_reçue ≤ sensibilité)";
   }


   return "__RESULT__|" . $resultat;