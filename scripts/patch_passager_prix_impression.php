<?php
/**
 * Applique la normalisation prixvente -> prix sur les retours row()/result() de Passager_model.
 */
$file = dirname(__DIR__) . '/application/models/Passager_model.php';
$content = file_get_contents($file);

if (strpos($content, 'normalize_ticket_prix_row') !== false) {
    echo "Already patched.\n";
    exit(0);
}

$normalizeMethods = <<<'PHP'

        private function normalize_ticket_prix_row($row)
        {
            return ticket_impression_prix_row($row);
        }

        private function normalize_ticket_prix_rows($rows)
        {
            return ticket_impression_prix_rows($rows);
        }

PHP;

$content = str_replace(
    "            parent::__construct();\n        }",
    "            parent::__construct();\n        }" . $normalizeMethods,
    $content,
    $count
);

if ($count !== 1) {
    fwrite(STDERR, "Could not insert normalize methods.\n");
    exit(1);
}

$len = strlen($content);
$offset = 0;
$replacements = 0;

while (($pos = strpos($content, '->row();', $offset)) !== false) {
    $start = strrpos(substr($content, 0, $pos), 'return $this->db->query(');
    if ($start === false) {
        $offset = $pos + 8;
        continue;
    }
    $queryStart = $start + strlen('return $this->db->query(');
    $querySql = substr($content, $queryStart, $pos - $queryStart);
    $querySql = rtrim($querySql);
    if (substr($querySql, -1) === ')') {
        $querySql = substr($querySql, 0, -1);
    }
    $replacement = '$row = $this->db->query(' . $querySql . ')->row(); return $this->normalize_ticket_prix_row($row);';
    $before = substr($content, 0, $start);
    $after = substr($content, $pos + 8);
    $content = $before . $replacement . $after;
    $offset = $start + strlen($replacement);
    $replacements++;
}

$offset = 0;
while (($pos = strpos($content, '->result();', $offset)) !== false) {
    $start = strrpos(substr($content, 0, $pos), 'return $this->db->query(');
    if ($start === false) {
        $offset = $pos + 12;
        continue;
    }
    $queryStart = $start + strlen('return $this->db->query(');
    $querySql = substr($content, $queryStart, $pos - $queryStart);
    $querySql = rtrim($querySql);
    if (substr($querySql, -1) === ')') {
        $querySql = substr($querySql, 0, -1);
    }
    $replacement = '$rows = $this->db->query(' . $querySql . ')->result(); return $this->normalize_ticket_prix_rows($rows);';
    $before = substr($content, 0, $start);
    $after = substr($content, $pos + 12);
    $content = $before . $replacement . $after;
    $offset = $start + strlen($replacement);
    $replacements++;
}

file_put_contents($file, $content);
echo "Patched Passager_model.php ($replacements replacements).\n";
