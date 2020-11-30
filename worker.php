<?php
$DEVELOPMENT = $_SERVER['HTTP_HOST']=='localhost';

function objs($obj, $key, $val, $except=[], &$objects=[]) {
  foreach($obj as $k=>$v) {
    if (is_null($v)) {
      continue;
    }
    if (is_object($v) || is_array($v)) {
      if (in_array($k, $except)) {
        continue;
      }
      objs($v, $key, $val, [], $objects);
    }
    // If key matches and value matches or if key matches and value is not passed (eliminating the case where key matches but passed value does not).
    elseif ($k==$key && ($v===$val || !$val)) {
      $objects[] = $obj;
    } elseif ($v===$val && !$key) {
      // Only add if the object is not already in the array.
      if (! in_array($obj, $objects)) {
        $objects[] = $obj;
      }
    }
  }
  return $objects;
}

function ability($data, $ability) {
  global $ABILITIES;
  $i = array_search($ability, ['strength', 'dexterity', 'constitution', 'intelligence', 'wisdom', 'charisma']);
  $base = $data->stats[$i]->value ?? 10;
  $bonus = $data->bonusStats[$i]->value ?? 0;
  $over = $data->overrideStats[$i]->value ?? 0;
  $total = $base + $bonus;
  $mods = objs($data, '', $ability.'-score');
  if ($over > 0) {
    $total = $over;
  }
  $done = [];
  foreach($mods as $m) {
    if ($m->type=='bonus' && !in_array($m->id, $done)) {
      $total += $m->value;
      $done[] = $m->id;
    }
  }
  return $total;
}

$curl = curl_init('https://character-service.dndbeyond.com/character/v4/character/'.$_GET['id']);
curl_setopt($curl, CURLOPT_FAILONERROR, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, !$DEVELOPMENT);
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$data = json_decode(curl_exec($curl))->data;

$level = 0;
foreach($data->classes as $class) {
  $level += $class->level;
}
$hp = floor($data->baseHitPoints + ($level * floor((ability($data, 'constitution') - 10) / 2)));

// Scan for modifiers except those in items, because we will get those bonuses from the items once they are imported.
// NOTE: this also handles the problem that Beyond includes modifiers from items that are not currently equipped/attuned.
foreach(objs($data->modifiers, 'subType', 'hit-points-per-level', ['item']) as $bonus) {
  $lv = $level;

  // Ensure that per-level bonuses from class features only apply for the levels of the class and not the character's total level.
  $classes = array_filter($data->classes, function($class) {
    foreach($class->definition->classFeatures as $i=>$v) {
      if ($v->id == $bonus->componentId) {
        return True;
      }
    }
    if (!$out && $class->subclassDefinition) {
      foreach($class->subclassDefinition->classFeatures as $i=>$v) {
        if ($v->id == $bonus->componentId) {
          return True;
        }
      }
    }
  });
  if ($classes) {
    $lv = 0;
    foreach($classes as $class) {
      $lv += intval($class->level);
    }
  }
  $hp += $lv * $bonus->value;
}
$max = $data->overrideHitPoints ?? $hp+$data->bonusHitPoints;

// The DDB API doesn't recalculate damage when the max HP gets modified/overridden down and the health is supposed to stay the same, so we have to compensate for that.
$cred = json_decode(file_get_contents('sqlcred.json'));
$sql = new mysqli($DEVELOPMENT ? 'localhost' : $cred->host, $cred->username, $cred->password, $cred->dbname);

$vals = $sql->query('SELECT max_orig, damage_orig, damage_fix FROM stats WHERE id='.$_GET['id'].';')->fetch_object();
$new = is_null($vals);
if ($new) {
  $vals = new stdClass;
  $vals->max_orig = NULL;
  $vals->damage_orig = NULL;
  $vals->damage_fix = NULL;
}
if (is_null($vals->damage_fix) && $data->removedHitPoints==$vals->damage_orig && $max<$vals->max_orig) {
  $vals->damage_fix = max(0, $vals->damage_orig+$max-$vals->max_orig);
} else if (!is_null($vals->damage_fix) && $data->removedHitPoints!=$vals->damage_orig) {
  $vals->damage_fix = NULL;
}
$vals->max_orig = $max;
$vals->damage_orig = $data->removedHitPoints;
if ($new) {
  $sql->query('INSERT INTO stats VALUES ('.$_GET['id'].', '.$vals->max_orig.', '.$vals->damage_orig.', '.var_export($vals->damage_fix, true).');');
} else {
  $q = $sql->query('UPDATE stats SET max_orig='.$vals->max_orig.', damage_orig='.$vals->damage_orig.', damage_fix='.var_export($vals->damage_fix, true).' WHERE id='.$_GET['id'].';');
}

header('Content-Type: application/json');
echo json_encode([$data->name, $max-($vals->damage_fix ?? $data->removedHitPoints), $max, $hp, $data->temporaryHitPoints]);
?>
