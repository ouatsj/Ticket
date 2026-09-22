#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Audit final: états in-app Rapport.php — cohérence / parité / filtres / PDF."""
from __future__ import print_function
import re
import os
import sys
from collections import defaultdict, OrderedDict

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
RAPPORT = os.path.join(ROOT, "application/controllers/Rapport.php")
MODELS = os.path.join(ROOT, "application/models")

src = open(RAPPORT, encoding="utf-8", errors="replace").read()
lines = src.splitlines()


def strip_comments(text):
    text = re.sub(r"/\*.*?\*/", "", text, flags=re.S)
    text = re.sub(r"//.*?$", "", text, flags=re.M)
    return text


# --- Method index ---
method_re = re.compile(r"^(\s*)(public|private|protected)\s+function\s+(\w+)\s*\(([^)]*)\)", re.M)
methods = []
for m in method_re.finditer(src):
    lineno = src[: m.start()].count("\n") + 1
    methods.append(
        {
            "vis": m.group(2),
            "name": m.group(3),
            "args": [a.strip() for a in m.group(4).split(",") if a.strip()],
            "start": lineno,
            "pos": m.start(),
        }
    )

for i, meth in enumerate(methods):
    end = methods[i + 1]["start"] - 1 if i + 1 < len(methods) else len(lines)
    meth["end"] = end
    meth["body"] = "\n".join(lines[meth["start"] - 1 : end])
    meth["body_nc"] = strip_comments(meth["body"])

by_name = {m["name"]: m for m in methods}

# --- Inventory ---
payloads = [m for m in methods if m["name"].endswith("_payload") and m["name"].startswith("_")]
exports = [m for m in methods if m["name"].endswith("_export")]
renders = []
for m in methods:
    if m["vis"] == "public" and not m["name"].endswith("_export"):
        if "_etat_render_view" in m["body"]:
            renders.append(m)

pdf_active = []
for m in methods:
    if re.search(r"->Output\s*\(|\bnew\s+Pdf\b|\bTCPDF\b", m["body_nc"], re.I):
        # ignore helpers that intentionally produce PDF export
        if m["name"] in ("_etat_output_pdf", "_etat_export_dispatch"):
            continue
        if "_etat_output_pdf" in m["body_nc"] or "_etat_export_dispatch" in m["body_nc"]:
            # export path OK
            if m["name"].endswith("_export"):
                continue
        pdf_active.append(m)

print("=" * 70)
print("INVENTAIRE")
print("=" * 70)
print("payloads:", len(payloads))
print("public render (_etat_render_view):", len(renders))
print("exports (*_export):", len(exports))
print("render_view calls:", len(re.findall(r"_etat_render_view\s*\(", src)))
print("export_dispatch calls:", len(re.findall(r"_etat_export_dispatch\s*\(", src)))
print("PDF actifs (hors helper export):", len(pdf_active))
for m in pdf_active:
    print("  !", m["name"], "L" + str(m["start"]))

# Pairing
print("\n" + "=" * 70)
print("APPARIEMENT render / payload / export")
print("=" * 70)
missing_export = []
missing_payload = []
orphan_export = []
for r in renders:
    name = r["name"]
    payload_name = "_" + name + "_payload"
    export_name = name + "_export"
    has_p = payload_name in by_name
    has_e = export_name in by_name
    if not has_p:
        # some share payloads (rare)
        if "_payload(" not in r["body"] and "->_" not in r["body"]:
            missing_payload.append(name)
        elif payload_name not in r["body"] and not re.search(r"_\w+_payload\s*\(", r["body"]):
            missing_payload.append(name)
    if not has_e:
        missing_export.append(name)

for e in exports:
    base = e["name"][: -len("_export")]
    if base not in {r["name"] for r in renders}:
        orphan_export.append(e["name"])

print("renders sans export sibling:", len(missing_export), missing_export[:20])
print("renders sans payload ref:", len(missing_payload), missing_payload[:20])
print("exports orphelins:", len(orphan_export), orphan_export[:20])

# --- Model calls in payloads ---
print("\n" + "=" * 70)
print("APPELS MODÈLES DANS PAYLOADS")
print("=" * 70)
model_call_re = re.compile(
    r"\$this->(m_\w+|entreprise)->(\w+)\s*\(([^;]*)\)",
    re.S,
)
# Also $this->M_xxx style
model_call_re2 = re.compile(r"\$this->([Mm]_\w+)->(\w+)\s*\(", re.S)

calls_by_payload = OrderedDict()
all_model_methods = defaultdict(set)
arity_issues = []

def count_args(argstr):
    # naive split respecting nested parens/quotes
    args = []
    depth = 0
    cur = []
    in_s = in_d = False
    esc = False
    for ch in argstr:
        if esc:
            cur.append(ch)
            esc = False
            continue
        if ch == "\\":
            cur.append(ch)
            esc = True
            continue
        if in_s:
            cur.append(ch)
            if ch == "'":
                in_s = False
            continue
        if in_d:
            cur.append(ch)
            if ch == '"':
                in_d = False
            continue
        if ch == "'":
            in_s = True
            cur.append(ch)
            continue
        if ch == '"':
            in_d = True
            cur.append(ch)
            continue
        if ch == "(":
            depth += 1
            cur.append(ch)
            continue
        if ch == ")":
            depth -= 1
            cur.append(ch)
            continue
        if ch == "," and depth == 0:
            a = "".join(cur).strip()
            if a:
                args.append(a)
            cur = []
            continue
        cur.append(ch)
    a = "".join(cur).strip()
    if a:
        args.append(a)
    return args

# Load model method signatures
model_sigs = {}  # model_file_hint -> method -> n_args (required approx)


def load_model_methods():
    for fn in os.listdir(MODELS):
        if not fn.endswith(".php"):
            continue
        p = os.path.join(MODELS, fn)
        text = open(p, encoding="utf-8", errors="replace").read()
        for mm in re.finditer(
            r"function\s+(\w+)\s*\(([^)]*)\)", text
        ):
            name = mm.group(1)
            raw = mm.group(2)
            parts = [x.strip() for x in raw.split(",") if x.strip()]
            # count required (no default)
            req = sum(1 for x in parts if "=" not in x)
            total = len(parts)
            model_sigs.setdefault(fn.lower(), {})[name] = (req, total, parts)


load_model_methods()

# Map $this->m_xxx to model file
alias_map = {
    "m_passager": "passager_model.php",
    "m_non_passager": "non_passager_model.php",
    "m_bagage": "bagage_model.php",
    "m_bagageesc": "bagageesc_model.php",
    "m_escalclients": "escalclients_model.php",
    "m_depense": "depense_model.php",
    "m_depot": "depot_model.php",
    "m_courriers_exp": "courriers_exp_model.php",
    "m_courriers_expesc": "courriers_expesc_model.php",
    "m_compte_user": "compte_user_model.php",
    "m_caisse": "caisse_model.php",
    "m_guichet": "guichet_model.php",
    "m_garedest": "garedest_model.php",
    "m_gare": "gare_model.php",
    "m_compagnies": "compagnies_model.php",
    "m_programmation": "programmation_model.php",
    "m_voyage": "voyage_model.php",
    "m_recette": "recette_model.php",
    "m_autredepense": "autredepense_model.php",
    "m_historique": "historique_model.php",
    "m_client": "client_model.php",
    "m_bus": "bus_model.php",
    "m_chauffeur": "chauffeur_model.php",
    "m_manifest": "manifest_model.php",
    "m_versement": "versement_model.php",
    "m_report": "report_model.php",
}

unknown_models = set()
missing_methods = []
arity_mismatches = []
call_count = 0

for p in payloads:
    body = p["body_nc"]
    calls = []
    # Find $this->m_xxx->method(
    for m in re.finditer(r"\$this->(m_\w+)->(\w+)\s*\(", body):
        model, meth = m.group(1), m.group(2)
        # extract args until matching paren
        start = m.end()
        depth = 1
        i = start
        while i < len(body) and depth:
            ch = body[i]
            if ch == "(":
                depth += 1
            elif ch == ")":
                depth -= 1
            i += 1
        argstr = body[start : i - 1]
        nargs = len(count_args(argstr)) if argstr.strip() else 0
        calls.append((model, meth, nargs, argstr[:80]))
        call_count += 1
        all_model_methods[model].add(meth)

        fname = alias_map.get(model)
        if not fname:
            # fuzzy
            cand = model[2:] + "_model.php" if model.startswith("m_") else None
            if cand and os.path.exists(os.path.join(MODELS, cand)):
                fname = cand.lower()
            else:
                # try title case
                for f in os.listdir(MODELS):
                    if f.lower().replace("_", "") == (model[2:] + "model").lower().replace("_", ""):
                        fname = f.lower()
                        break
        if not fname:
            unknown_models.add(model)
            continue
        sigs = model_sigs.get(fname.lower()) or model_sigs.get(fname)
        # try case-insensitive file key
        if not sigs:
            for k, v in model_sigs.items():
                if k.lower() == fname.lower():
                    sigs = v
                    break
        if not sigs or meth not in sigs:
            # method might be in parent / different file
            found = False
            for k, v in model_sigs.items():
                if meth in v:
                    found = True
                    req, total, parts = v[meth]
                    if nargs < req or nargs > total:
                        arity_mismatches.append(
                            (p["name"], model, meth, nargs, req, total, "alt:" + k)
                        )
                    break
            if not found:
                missing_methods.append((p["name"], model, meth, nargs))
            continue
        req, total, parts = sigs[meth]
        if nargs < req or nargs > total:
            arity_mismatches.append(
                (p["name"], model, meth, nargs, req, total, fname)
            )
    calls_by_payload[p["name"]] = calls

print("appels modèles dans payloads:", call_count)
print("modèles non mappés:", sorted(unknown_models)[:30], "count=", len(unknown_models))
print("méthodes introuvables:", len(missing_methods))
for x in missing_methods[:25]:
    print("  ?", x)
print("arity mismatches:", len(arity_mismatches))
for x in arity_mismatches[:40]:
    print("  !", x)

# --- Filters / get_post ---
print("\n" + "=" * 70)
print("FILTRES (get_post / POST keys)")
print("=" * 70)
post_keys = set(re.findall(r"get_post\s*\(\s*['\"](\w+)['\"]", src))
input_keys = set(re.findall(r"\$this->input->(?:get_post|post|get)\s*\(\s*['\"](\w+)['\"]", src))
print("get_post keys:", len(post_keys))
print("sorted:", ", ".join(sorted(post_keys)))

# Retour hiddens
retour_url = "_etat_retour_url" in src
normalize = "_normalize_recap_gare_code_filter" in src
print("helper _etat_retour_url:", retour_url)
print("helper _normalize_recap_gare_code_filter:", normalize)

# Check each payload has filters + columns + rows structure
schema_issues = []
for p in payloads:
    b = p["body"]
    has_cols = "'columns'" in b or '"columns"' in b
    has_rows = "'rows'" in b or '"rows"' in b
    has_title = "'title'" in b or '"title"' in b
    has_export = "'export_base'" in b
    if not (has_cols and has_rows):
        schema_issues.append((p["name"], has_cols, has_rows, has_title, has_export))

print("\npayloads schéma incomplet (columns/rows):", len(schema_issues))
for x in schema_issues[:30]:
    print("  !", x)

# --- doUpdate mutation safety ---
print("\n" + "=" * 70)
print("MUTATIONS doUpdate")
print("=" * 70)
for p in payloads:
    if "doUpdate" in p["body"] or "doUpdate" in str(p["args"]):
        # export should pass false
        base = p["name"][1 : -len("_payload")] if p["name"].startswith("_") else p["name"]
        exp = by_name.get(base + "_export")
        pub = by_name.get(base)
        print(p["name"], "args=", p["args"])
        if pub:
            # find payload call
            calls = re.findall(r"_" + re.escape(base) + r"_payload\s*\(([^)]*)\)", pub["body"])
            print("  public calls:", calls)
        if exp:
            calls = re.findall(r"_" + re.escape(base) + r"_payload\s*\(([^)]*)\)", exp["body"])
            print("  export calls:", calls)

# --- Compare with HEAD if git available ---
print("\n" + "=" * 70)
print("PARITÉ vs git HEAD (appels modèles dans méthodes publiques converties)")
print("=" * 70)
import subprocess

def git_show(rev_path):
    try:
        out = subprocess.check_output(
            ["git", "show", rev_path], cwd=ROOT, stderr=subprocess.DEVNULL
        )
        return out.decode("utf-8", "replace")
    except Exception:
        return None

# Try to find an older version - maybe from backup or git
head = git_show("HEAD:application/controllers/Rapport.php")
# Also try common backup names
backups = []
for cand in [
    "application/controllers/Rapport.php.bak",
    "application/controllers/Rapport.php.old",
    "application/controllers/Rapport.php.pdfbak",
]:
    if os.path.exists(os.path.join(ROOT, cand)):
        backups.append(cand)

print("git HEAD Rapport available:", head is not None and len(head) > 1000)
print("backup files:", backups)

# Extract model calls from a method body (active code only)
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
        nargs = len(count_args(argstr)) if argstr.strip() else 0
        res.append((model, meth, nargs))
    return res

# If we have patch scripts, they often embed OLD method snippets - use git log
# Compare current public method's payload calls vs any remaining commented PDF blocks in same file

commented_pdf_calls = []
# Find large commented blocks that look like old PDF
for m in methods:
    if m["name"].endswith("_payload"):
        continue
    # look for commented model calls in body (still in // form)
    raw = m["body"]
    for line in raw.splitlines():
        s = line.strip()
        if s.startswith("//") and "->" in s and "m_" in s:
            mm = re.search(r"\$this->(m_\w+)->(\w+)\s*\(", s)
            if mm:
                commented_pdf_calls.append((m["name"], mm.group(1), mm.group(2)))

print("commented model calls leftover:", len(commented_pdf_calls))

# Role branches: check ROLE / idrole patterns preserved
print("\n" + "=" * 70)
print("BRANCHES RÔLES")
print("=" * 70)
role_payloads = []
for p in payloads:
    if re.search(r"idrole|role\s*==|case\s+['\"]?\d+", p["body_nc"], re.I):
        role_payloads.append(p["name"])
print("payloads avec branches rôle:", len(role_payloads))

# Compagnie special cases 5002 etc
comp_special = []
for p in payloads:
    if "5002" in p["body"] or "5001" in p["body"] or "compagnie" in p["body_nc"].lower():
        if re.search(r"5002|5001|ekey\s*==", p["body_nc"]):
            comp_special.append(p["name"])
print("payloads branches compagnie spéciales:", len(comp_special))
for n in comp_special:
    print(" ", n)

# Filter field name stability: collect get_post in each payload
filter_drift = []
common_filters = defaultdict(int)
for p in payloads:
    keys = re.findall(r"get_post\s*\(\s*['\"](\w+)['\"]", p["body"])
    for k in keys:
        common_filters[k] += 1

print("\nfiltre fréquence top:")
for k, c in sorted(common_filters.items(), key=lambda x: -x[1])[:25]:
    print(" ", k, c)

# JS cascade
js_path = os.path.join(ROOT, "assets/js/tri-filtre-dynamique.js")
if not os.path.exists(js_path):
    # find
    for root, dirs, files in os.walk(ROOT):
        if "tri-filtre-dynamique.js" in files:
            js_path = os.path.join(root, "tri-filtre-dynamique.js")
            break
print("\ntri-filtre-dynamique.js:", js_path, "exists=", os.path.exists(js_path))

# Views Retour
views = []
for root, dirs, files in os.walk(os.path.join(ROOT, "application/views")):
    for f in files:
        if "etat" in f.lower() or "rapport" in f.lower():
            views.append(os.path.join(root, f))
print("views rapport/etat:", len(views))

# Final score summary
print("\n" + "=" * 70)
print("SCORE")
print("=" * 70)
issues = []
if pdf_active:
    issues.append(("PDF actifs restants", len(pdf_active)))
if missing_export:
    issues.append(("renders sans export", len(missing_export)))
if schema_issues:
    issues.append(("payloads schéma incomplet", len(schema_issues)))
if arity_mismatches:
    issues.append(("arity mismatches", len(arity_mismatches)))
# missing_methods may include false positives for dynamic models
print("ALERTES:", issues if issues else "aucune alerte bloquante")
print("WARN méthodes modèle non résolues:", len(missing_methods))
print("OK payloads:", len(payloads), "renders:", len(renders), "exports:", len(exports))
