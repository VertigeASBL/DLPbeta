<?
reset($_POST);
while (list($k1, $v1) = each($_POST))
	if (is_array($v1))
		while (list($k2, $v2) = each($v1))
			if (is_array($v2))
				while (list($k3, $v3) = each($v2))
					if (is_array($v3))
						while (list($k4, $v4) = each($v3))
							${$k1}[$k2][$k3][$k4] = addslashes($v4);
					else
						${$k1}[$k2][$k3] = addslashes($v3);
			else
				${$k1}[$k2] = addslashes($v2);
	else {
		$$k1 = addslashes($v1);
		if (isset($contexte[$k1]))
			unset($contexte[$k1]);
	}
reset($_FILES);
while (list($k1, $v1) = each($_FILES)) {
	unset($$k1);
	if (is_array($v1))
		while (list($k2, $v2) = each($v1))
			if (is_array($v2))
				while (list($k3, $v3) = each($v2))
					${$k1}[$k2][$k3] = $v3;
			else
				${$k1}[$k2] = $v2;
	else
		$$k1 = $v1;
}
unset($k1, $k2, $k3, $k4, $v1, $v2, $v3, $v4);

if (isset($contexte['id'])) $id = $contexte['id'];
if (isset($contexte['seln'])) $seln = $contexte['seln'];
if (isset($contexte['metier'])) $metier = $contexte['metier'];
if (isset($contexte['compagnie'])) $compagnie = $contexte['compagnie'];
if (isset($contexte['etat'])) $etat = $contexte['etat'];
if (isset($contexte['ext'])) $ext = $contexte['ext'];

if (isset($id) && strlen($id) != 20)
	$id = 'x';
if (isset($seln) && ! is_numeric($seln))
	$seln = 0;
?>
