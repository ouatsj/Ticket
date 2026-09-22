#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Parité WT payloads vs HEAD PDF — lecture locale uniquement."""
from __future__ import print_function
import re
import subprocess
import os
from collections import Counter, defaultdict

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
os.chdir(ROOT)


def strip(t):
    t = re.sub(r"/\*.*?\*/", "", t, flags=re.S)
    t = re.sub(r"//.*?$", "", t, flags=re.M)
    return t


def parse_methods(src):
    ms = []
    for m in re.finditer(
        r"(public|private|protected)\s+function\s+(\w+)\s*\(([^)]*)\)", src
    ):
        ms.append(
            {
                "vis": m.group(1),
                "name": m.group(2),
                "start": src[: m.start()].count("\n") + 1,
            }
        )
    lines = src.splitlines()
    out = {}
    for i, meth in enumerate(ms):
        end = ms[i + 1]["start"] - 1 if i + 1 < len(ms) else len(lines)
        body = "\n".join(lines[meth["start"] - 1 : end])
        meth["body"] = body
        meth["body_nc"] = strip(body)
        out[meth["name"]] = meth
    return out


def extract_calls(body_nc):
    res = []
    for m in re.finditer(r"\$this->(m_\w+)->(\w+)\s*\(", body_nc):
        model, meth = m.group(1), m.group(2)
        start = m.end()
        depth = 1
        i = start
        while i < len(body_nc) and depth:
            if body_nc[i] == "(":
                depth += 1
            elif body_nc[i] == ")":
                depth -= 1
            i += 1
        argstr = body_nc[start : i - 1]
        n = 0
        if argstr.strip():
            depth = 0
            ins = ind = False
            esc = False
            n = 1
            for ch in argstr:
                if esc:
                    esc = False
                    continue
                if ch == "\\":
                    esc = True
                    continue
                if ins:
                    if ch == "'":
                        ins = False
                    continue
                if ind:
                    if ch == '"':
                        ind = False
                    continue
                if ch == "'":
                    ins = True
                    continue
                if ch == '"':
                    ind = True
                    continue
                if ch == "(":
                    depth += 1
                elif ch == ")":
                    depth -= 1
                elif ch == "," and depth == 0:
                    n += 1
        res.append((model, meth, n))
    return res


IGNORE_METH = {
    ("m_entreprises", "get_key"),
    ("m_gare_depart", "getn"),
    ("m_gare_arrivee", "getn"),
    ("m_compagnies", "getn"),
    ("m_compte_user", "cpusers"),
    ("m_compte_user", "get"),
    ("m_utilisateur", "u"),
    ("m_sousgare", "getn"),
    ("m_sousgare", "getsousgare"),
    ("m_sousgare", "get"),
}

ALIAS = {
    ("m_depense", "trisdepens"): ("m_depense", "trisdepens_par_profil"),
    ("m_depot", "trisdepot"): ("m_depot", "trisdepot_par_profil"),
}


def filt(calls):
    return Counter((a, b, c) for a, b, c in calls if (a, b) not in IGNORE_METH)


cur = open("application/controllers/Rapport.php", encoding="utf-8", errors="replace").read()
head = subprocess.check_output(
    ["git", "show", "HEAD:application/controllers/Rapport.php"]
).decode("utf-8", "replace")
cm = parse_methods(cur)
hm = parse_methods(head)

renders = []
for name, m in cm.items():
    if (
        m["vis"] == "public"
        and not name.endswith("_export")
        and "_etat_render_view" in m["body"]
    ):
        renders.append(name)

print("etats in-app WT:", len(renders))

real_drops = []
arity_changes = []
ok_list = []
no_head = []

for name in sorted(renders):
    payload = "_" + name + "_payload"
    if payload in cm:
        new_calls = extract_calls(cm[payload]["body_nc"])
    else:
        new_calls = extract_calls(cm[name]["body_nc"])
    new_calls += extract_calls(cm[name]["body_nc"])

    if name not in hm:
        no_head.append(name)
        continue
    old_calls = extract_calls(hm[name]["body_nc"])

    oc = filt(old_calls)
    nc = filt(new_calls)
    old_meths = {(a, b) for a, b, _ in oc}
    new_meths = {(a, b) for a, b, _ in nc}

    dropped = old_meths - new_meths
    real = []
    for d in dropped:
        if d in ALIAS and ALIAS[d] in new_meths:
            continue
        real.append(d)
    if real:
        real_drops.append(
            (
                name,
                real,
                [(a, b, c) for (a, b, c) in oc if (a, b) in real],
            )
        )

    for a, b in old_meths & new_meths:
        old_ar = sorted({c for x, y, c in oc if (x, y) == (a, b)})
        new_ar = sorted({c for x, y, c in nc if (x, y) == (a, b)})
        if old_ar != new_ar:
            arity_changes.append((name, a, b, old_ar, new_ar))

    if not real and not any(x[0] == name for x in arity_changes):
        ok_list.append(name)

print("\n=== VRAIES METHODES DATA DROPPÉES ===")
print("count etats:", len(real_drops))
for name, real, oldc in real_drops:
    print(name, "DROPPED", real, "old", oldc)

print("\n=== CHANGEMENTS ARITE ===")
print("count:", len(arity_changes))
for x in arity_changes:
    print(" !", x)

# model signatures for arity
sigs = {}
models_dir = "application/models"
for fn in os.listdir(models_dir):
    if not fn.endswith(".php"):
        continue
    text = open(os.path.join(models_dir, fn), encoding="utf-8", errors="replace").read()
    for mm in re.finditer(r"function\s+(\w+)\s*\(([^)]*)\)", text):
        parts = [p.strip() for p in mm.group(2).split(",") if p.strip()]
        sigs[mm.group(1)] = (len(parts), parts, fn)

print("\n--- Detail arity vs signatures modeles ---")
for name, a, b, old_ar, new_ar in arity_changes:
    s = sigs.get(b)
    print(
        name,
        a,
        b,
        "old",
        old_ar,
        "new",
        new_ar,
        "model_params",
        s[0] if s else "?",
        s[2] if s else "?",
    )
    if s:
        print("  ", ", ".join(s[1]))

print("\nABSENTS DE HEAD:", no_head)
print("PARITE OK:", len(ok_list), "/", len(renders))
bad = set([x[0] for x in real_drops]) | set([x[0] for x in arity_changes])
print("avec issues:", len(bad))
for n in sorted(bad):
    print(" -", n)

# Filter name stability: compare get_post keys in HEAD public vs WT payload
print("\n=== FILTRES get_post / post keys (HEAD vs WT) ===")
filter_issues = []
for name in sorted(renders):
    if name not in hm:
        continue
    payload = "_" + name + "_payload"
    body_new = cm[payload]["body"] if payload in cm else cm[name]["body"]
    body_old = hm[name]["body"]
    old_keys = set(
        re.findall(r"(?:get_post|post|get)\s*\(\s*['\"](\w+)['\"]", body_old)
    )
    new_keys = set(
        re.findall(r"(?:get_post|post|get)\s*\(\s*['\"](\w+)['\"]", body_new)
    )
    # ignore export-only / retour helpers added
    ignore = {"format"}
    lost = (old_keys - new_keys) - ignore
    # keys only used in PDF title construction sometimes move - flag lost data filters
    if lost:
        filter_issues.append((name, sorted(lost), sorted(new_keys - old_keys)[:10]))

print("etats avec filtres perdus vs HEAD:", len(filter_issues))
for name, lost, gained in filter_issues[:40]:
    print(" !", name, "LOST", lost, "gained_sample", gained)
