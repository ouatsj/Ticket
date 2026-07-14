#!/usr/bin/env python3
"""Retire les blocs reservation R dupliques dans Programmes.php (addpassager)."""
from pathlib import Path

path = Path(__file__).resolve().parents[1] / 'application/controllers/Programmes.php'
lines = path.read_text(encoding='utf-8', errors='replace').splitlines(keepends=True)

MARKER = "$dte = date('H:i', time('H:i')+3600)"
END_MARKER = "$this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray)"

out = []
i = 0
removed = 0
while i < len(lines):
    if MARKER in lines[i]:
        j = i
        found_end = False
        while j < len(lines):
            if END_MARKER in lines[j]:
                # saute jusqu'à la fermeture du foreach (ligne avec seulement })
                k = j + 1
                while k < len(lines) and lines[k].strip() != '}':
                    k += 1
                if k < len(lines):
                    i = k + 1
                    removed += 1
                    found_end = True
                break
            j += 1
        if not found_end:
            out.append(lines[i])
            i += 1
    else:
        out.append(lines[i])
        i += 1

path.write_text(''.join(out), encoding='utf-8')
print(f'Blocs reservation R retires: {removed}')
