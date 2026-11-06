#!/usr/bin/env python3
"""Konversi dokumen .docx formulir BNSP menjadi template HTML (folder `html/`).

Menjaga struktur tabel (colspan/rowspan, lebar kolom), teks tebal/miring,
gambar, kotak centang (☐), serta mengubah sel kosong menjadi isian teks.
Tanpa dependensi luar: cukup `python docx2html.py`.
"""
import html
import json
import os
import re
import shutil
import zipfile
from xml.etree import ElementTree as ET

W = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}'
R = '{http://schemas.openxmlformats.org/officeDocument/2006/relationships}'
A = '{http://schemas.openxmlformats.org/drawingml/2006/main}'
MC = '{http://schemas.openxmlformats.org/markup-compatibility/2006}'
V = '{urn:schemas-microsoft-com:vml}'
WP = '{http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing}'

OUT = 'html'
ASSETS = os.path.join(OUT, 'assets')

# Sudah punya template tulis tangan (lebih interaktif) -> jangan ditimpa.
SKIP = {
    '7. FR.AK.01. Persetujuan Asesmen dan Kerahasian  Rev 19 docx.docx':
        'FR.AK.01_Persetujuan_Asesmen_dan_Kerahasiaan.html',
    '8. FR.AK.04. Banding Asesmen.docx': 'FR.AK.04_Banding_Asesmen.html',
    '9. FR. AK.07-Penyesuian-wajar-alasan.docx': 'FR.AK.07_Penyesuaian_Wajar_dan_Beralasan.html',
    '000. PERANGKAT COVER MUK.docx': '000_Perangkat_COVER_MUK.html',
}


def esc(s):
    return html.escape(s, quote=False)


def plain(el):
    return re.sub(r'\s+', ' ', ''.join(t.text or '' for t in el.iter(W + 't'))).strip()


def slug(s, maxlen=48):
    s = re.sub(r'[^a-z0-9]+', '_', s.lower()).strip('_')
    return s[:maxlen].rstrip('_') or 'field'


def table_rows(tbl):
    """Baris tabel, termasuk yang dibungkus content control (w:sdt)."""
    rows = []
    for child in tbl:
        if child.tag == W + 'tr':
            rows.append(child)
        elif child.tag in (W + 'sdt', W + 'customXml'):
            content = child.find(W + 'sdtContent')
            node = content if content is not None else child
            rows.extend(node.findall(W + 'tr'))
    return rows


def row_cells(tr):
    """Sel baris, termasuk yang dibungkus content control (w:sdt)."""
    cells = []
    for child in tr:
        if child.tag == W + 'tc':
            cells.append(child)
        elif child.tag in (W + 'sdt', W + 'customXml'):
            content = child.find(W + 'sdtContent')
            node = content if content is not None else child
            cells.extend(node.findall(W + 'tc'))
    return cells


def run_of_text(txt):
    """Teks -> HTML, kotak centang unicode jadi checkbox asli."""
    out = esc(txt)
    for c in ('☐', '□', '❑'):
        out = out.replace(c, '<input type="checkbox">')
    return out


class Doc:
    def __init__(self, path):
        self.path = path
        self.zip = zipfile.ZipFile(path)
        self.root = ET.fromstring(self.zip.read('word/document.xml'))
        self.rels = self._rels()
        self.used = {}
        self.asset_n = 0
        self.prefix = 'form'
        self.code = ''
        self.title = os.path.splitext(os.path.basename(path))[0]

    def _rels(self):
        try:
            data = self.zip.read('word/_rels/document.xml.rels')
        except KeyError:
            return {}
        rels = {}
        for rel in ET.fromstring(data):
            target = rel.get('Target', '')
            if 'media/' in target:
                rels[rel.get('Id')] = 'word/' + target.lstrip('/').replace('word/', '')
        return rels

    def uniq(self, base):
        base = base or 'field'
        n = self.used.get(base, 0) + 1
        self.used[base] = n
        return base if n == 1 else f'{base}_{n}'

    # ---------------- gambar / textbox ----------------
    def image(self, el):
        rid = None
        blip = next(el.iter(A + 'blip'), None)
        if blip is not None:
            rid = blip.get(R + 'embed')
        if not rid:
            for im in el.iter(V + 'imagedata'):
                rid = im.get(R + 'id')
                if rid:
                    break
        if not rid or rid not in self.rels:
            return ''
        src = self.rels[rid]
        ext = os.path.splitext(src)[1] or '.png'
        self.asset_n += 1
        name = f'{self.prefix}_img{self.asset_n}{ext}'
        os.makedirs(ASSETS, exist_ok=True)
        with open(os.path.join(ASSETS, name), 'wb') as fh:
            fh.write(self.zip.read(src))
        width = ''
        extent = next(el.iter(WP + 'extent'), None)
        if extent is not None and extent.get('cx'):
            mm = int(extent.get('cx')) / 36000  # EMU -> mm
            if mm >= 5:
                width = f' style="width:{mm:.0f}mm"'
        return f'<img class="gbr" src="assets/{name}" alt=""{width}>'

    def textbox(self, box):
        inner = ''.join(self.blocks(box))
        return f'<div class="txbx">{inner}</div>' if inner.strip() else ''

    # ---------------- run / paragraf ----------------
    def run(self, r):
        """-> (kunci format, html isi) agar run seformat bisa digabung."""
        rpr = r.find(W + 'rPr')
        bold = rpr is not None and rpr.find(W + 'b') is not None
        ital = rpr is not None and rpr.find(W + 'i') is not None
        und = rpr is not None and rpr.find(W + 'u') is not None
        pt = None
        if rpr is not None:
            sz = rpr.find(W + 'sz')
            if sz is not None:
                try:
                    pt = int(sz.get(W + 'val')) / 2
                except (TypeError, ValueError):
                    pt = None
        pieces = []
        for child in r:
            tag = child.tag
            if tag == W + 't':
                pieces.append(run_of_text(child.text or ''))
            elif tag == W + 'tab':
                pieces.append('<span class="tab"></span>')
            elif tag in (W + 'br', W + 'cr'):
                pieces.append('<br>')
            elif tag in (W + 'drawing', W + 'pict'):
                pieces.append(self.shape(child))
            elif tag == MC + 'AlternateContent':
                for alt in child:
                    for shp in alt:
                        if shp.tag in (W + 'drawing', W + 'pict'):
                            pieces.append(self.shape(shp))
        return (bold, ital, und, pt), ''.join(pieces)

    def shape(self, el):
        """Gambar atau kotak teks di dalam shape/drawing."""
        box = next(el.iter(W + 'txbxContent'), None)
        return self.textbox(box) if box is not None else self.image(el)

    @staticmethod
    def wrap(key, body):
        bold, ital, und, pt = key
        if bold:
            body = f'<b>{body}</b>'
        if ital:
            body = f'<i>{body}</i>'
        if und:
            body = f'<span class="u">{body}</span>'
        if pt and pt != 11:
            body = f'<span style="font-size:{pt:g}pt">{body}</span>'
        return body

    def para(self, p):
        parts, cls = [], ''
        ppr = p.find(W + 'pPr')
        if ppr is not None:
            jc = ppr.find(W + 'jc')
            if jc is not None and jc.get(W + 'val') == 'center':
                cls = ' class="c"'
            elif ppr.find(W + 'numPr') is not None:
                cls = ' class="li"'
        groups = []  # [(kunci, [html, ...])]
        runs = []
        for child in p:
            if child.tag == W + 'r':
                runs.append(child)
            elif child.tag in (W + 'hyperlink', W + 'ins', W + 'smartTag',
                               W + 'fldSimple', W + 'sdt'):
                runs.extend(child.iter(W + 'r'))
        for r in runs:
            key, body = self.run(r)
            if not body:
                continue
            if groups and groups[-1][0] == key:
                groups[-1][1].append(body)
            else:
                groups.append((key, [body]))
        parts = [self.wrap(k, ''.join(v)) for k, v in groups]
        txt = ''.join(parts)
        if not txt.strip() and '<img' not in txt:
            return ''
        return f'<p{cls}>{txt}</p>'

    # ---------------- tabel ----------------
    def table(self, tbl):
        grid = [int(g.get(W + 'w', 0) or 0) for g in tbl.iter(W + 'gridCol')]
        total = sum(grid) or 1
        rows = table_rows(tbl)
        head, body = [], []
        for ri, tr in enumerate(rows):
            tcs = row_cells(tr)
            label = ''
            cells, col = [], 0
            for tc in tcs:
                tcpr = tc.find(W + 'tcPr')
                span, vm, width = 1, '', 0
                if tcpr is not None:
                    gs = tcpr.find(W + 'gridSpan')
                    if gs is not None:
                        span = int(gs.get(W + 'val', 1) or 1)
                    w_el = tcpr.find(W + 'tcW')
                    if w_el is not None and (w_el.get(W + 'w') or '').isdigit():
                        width = int(w_el.get(W + 'w'))
                    v = tcpr.find(W + 'vMerge')
                    if v is not None:
                        vm = 'cont' if (v.get(W + 'val') or '') == 'continue' else 'restart'
                start = col
                col += span
                if vm == 'cont':
                    continue
                rowspan = 1
                if vm == 'restart':
                    for rj in range(ri + 1, len(rows)):
                        acc, cont = 0, False
                        for t2 in row_cells(rows[rj]):
                            p2 = t2.find(W + 'tcPr')
                            s2 = 1
                            if p2 is not None and p2.find(W + 'gridSpan') is not None:
                                s2 = int(p2.find(W + 'gridSpan').get(W + 'val', 1) or 1)
                            if acc == start:
                                v2 = p2.find(W + 'vMerge') if p2 is not None else None
                                cont = v2 is not None and (v2.get(W + 'val') or '') == 'continue'
                                break
                            acc += s2
                        if not cont:
                            break
                        rowspan += 1
                attrs = ''
                if span > 1:
                    attrs += f' colspan="{span}"'
                if rowspan > 1:
                    attrs += f' rowspan="{rowspan}"'
                if width:
                    attrs += f' style="width:{width / total * 100:.1f}%"'
                cells.append(self.cell(tc, attrs, ri, start, label))
                t = plain(tc)
                if not label and t and not re.fullmatch(r':+', t):
                    label = t
            if not cells:
                continue
            row = f"<tr>{''.join(cells)}</tr>"
            trpr = tr.find(W + 'trPr')
            is_head = trpr is not None and trpr.find(W + 'tblHeader') is not None
            (head if is_head else body).append(row)
        colgroup = ''
        if total > 1 and all(grid):
            colgroup = '<colgroup>' + ''.join(
                f'<col style="width:{c / total * 100:.1f}%">' for c in grid) + '</colgroup>'
        thead = f'<thead>{head[-1]}</thead>' if head else ''
        return f'<table>{colgroup}{thead}{"".join(body)}</table>'

    def cell(self, tc, attrs, ri, ci, label):
        txt = plain(tc)
        if txt and re.fullmatch(r':+', txt):
            return f'<td{attrs} class="sep">:</td>'
        has_obj = tc.find('.//' + W + 'drawing') is not None or tc.find('.//' + W + 'pict') is not None
        has_tbl = any(True for _ in tc.iter(W + 'tbl'))
        if (not txt and not has_obj and not has_tbl) or not txt.strip(' ._…·'):
            name = self.uniq(slug(label) if label else f'f{ri}_{ci}')
            return f'<td{attrs}><input type="text" name="{name}"></td>'
        inner = ''.join(self.blocks(tc))
        # semua isi sel paragraf saja -> lepas pembungkus <p> agar sel rapi
        if inner.startswith('<p') and inner.endswith('</p>') and inner.count('<p') == 1:
            inner = re.sub(r'^<p[^>]*>', '', inner, count=1)[:-4]
        elif inner.startswith('<p') and '<table' not in inner and '<div' not in inner:
            inner = re.sub(r'</p>\s*<p[^>]*>', '<br>', inner)
            inner = re.sub(r'^<p[^>]*>', '', inner, count=1)[:-4] if inner.endswith('</p>') else inner
        return f'<td{attrs}>{inner}</td>'

    # ---------------- blok ----------------
    def blocks(self, parent):
        out = []
        for child in parent:
            tag = child.tag
            if tag == W + 'p':
                out.append(self.para(child))
            elif tag == W + 'tbl':
                out.append(self.table(child))
            elif tag == W + 'sdt':
                content = child.find(W + 'sdtContent')
                if content is not None:
                    out.extend(self.blocks(content))
            elif tag == MC + 'AlternateContent':
                choice = child.find(MC + 'Choice')
                if choice is not None:
                    out.extend(self.blocks(choice))
            elif tag == MC + 'Fallback':
                continue
        return [x for x in out if x]


def make_heading(block):
    """Paragraf judul (mis. 'FR.APL.01. PERMOHONAN ...') -> tabel header 2 kolom."""
    text = re.sub(r'<[^>]+>', '', block)
    text = html.unescape(text).strip()
    m = re.match(r'^((?:FR|FORMULIR|DAFTAR|CEKLIS|CEK LIS|PETA|PERANGKAT|BAHAN)\b[\w.\s/-]*?'
                 r'\d{0,2}[A-Z]?\s*[.\-:]?)\s+(.+)$', text, re.I)
    if m:
        code = re.sub(r'[\s]+', '', m.group(1)).strip('.-:')
        title = m.group(2).strip()
    else:
        m = re.match(r'^(BAHAN\s+SOAL)\b\s*(.*)$', text, re.I)
        if not m:
            return None
        code, title = m.group(1), m.group(2) or 'BAHAN SOAL'
    if len(title) > 160:
        return None
    return (f'<table class="hd"><tr><td>{esc(code)}</td><td>{esc(title.upper())}</td></tr></table>'
            '<div class="gap"></div>'), code


KECIL = {'dan', 'atau', 'di', 'dalam', 'untuk', 'pada', 'yang', 'dengan', 'ke', 'dari'}


def title_case(text):
    words = re.sub(r'[^0-9A-Za-z]+', ' ', text).strip().split()
    out = []
    for i, w in enumerate(words):
        if i and w.lower() in KECIL:
            out.append(w.lower())
        elif w.isupper() and len(w) <= 5:
            out.append(w)
        elif w.isdigit():
            out.append(w)
        else:
            out.append(w[0].upper() + w[1:].lower())
    return '_'.join(out)


def build_name(base):
    """Nama berkas dari nama dokumen: <KODE>_<Judul_Kata_Kunci>.html."""
    s = base.strip()
    nomor = ''
    m = re.match(r'^(\d+)\s*[\._-]*\s*', s)
    if m:
        nomor = m.group(1)
        if nomor.strip('0') == '':  # "0. " bukan penanda urutan; "000" tetap dipakai
            nomor = nomor if len(nomor) > 1 else ''
        s = s[m.end():]
    s = re.sub(r'^[a-z]\.\s*(?=FR)', '', s, flags=re.I)  # "a.FR.IA.04A"
    s = re.sub(r'^[\s._-]+', '', s)
    m = re.match(r'FR[\s.]*([A-Z]{2,4})\s*\.?\s*(\d{1,2})\s*\.?\s*(?:([A-Z])(?=[\s.]))?\s*\.?\s*(.*)$', s)
    if m:
        kode = f'FR.{m.group(1)}.{m.group(2)}' + (f'.{m.group(3)}' if m.group(3) else '')
        judul = title_case(m.group(4))
    else:
        m = re.match(r'FR[\s.]*([A-Z]{2,4})\s*\.?\s*(.*)$', s)
        if m:
            kode = f'FR.{m.group(1)}'
            judul = title_case(m.group(2))
        else:
            kode = nomor
            judul = title_case(s)
    kata = [w for w in judul.split('_') if w and w.lower() != 'docx'][:7]
    judul = '_'.join(kata)
    nama = f'{kode}_{judul}' if kode else judul
    return (nama or 'Formulir') + '.html', kode, judul.replace('_', ' ')


def convert(path):
    doc = Doc(path)
    base = os.path.splitext(os.path.basename(path))[0]
    code_m = re.search(r'FR[\s.]*?(?:APL|IA|AK|MAPA|VA)[\s.\-]*\d{1,2}\s*\.?\s*[A-Za-z]?', base, re.I)
    doc.code = re.sub(r'\s+', '', code_m.group(0)).strip('.') if code_m else base.split('.')[0].strip()
    doc.prefix = slug(doc.code, 24)
    inner = doc.blocks(doc.root.find(W + 'body'))

    dest_name, kode, judul = build_name(base)
    heading = ''
    if inner:
        made = make_heading(inner[0])
        if made:
            heading = made[0]
            inner = inner[1:]
    if not heading:
        heading = (f'<table class="hd"><tr><td>{esc(kode)}</td>'
                   f'<td>{esc(judul.upper())}</td></tr></table><div class="gap"></div>')

    tpl = f'''<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{esc(kode)} {esc(judul)}</title><link rel="stylesheet" href="form.css"></head>
<body>
<div class="actions"><button onclick="print()">Cetak / Simpan PDF</button></div>
<div class="page"><form>
{heading}{''.join(inner)}
</form></div></body></html>
'''
    with open(os.path.join(OUT, dest_name), 'w', encoding='utf-8') as fh:
        fh.write(tpl)
    return {'file': dest_name, 'kode': kode or 'Dokumen', 'judul': judul, 'sumber': base}


KELOMPOK = [
    ('APL', 'Permohonan & Perencanaan'),
    ('MAPA', 'Permohonan & Perencanaan'),
    ('IA', 'Instrumen Asesmen'),
    ('AK', 'Keputusan & Rekaman Asesmen'),
    ('VA', 'Validasi Asesmen'),
]


def kelompok(kode):
    m = re.match(r'FR[\._]?([A-Z]{2,4})', kode or '')
    if m:
        for key, label in KELOMPOK:
            if key == m.group(1):
                return label
        return 'Formulir Lainnya'
    return 'Dokumen Pendukung'


def write_index(entries):
    urut = {'Dokumen Pendukung': 0, 'Permohonan & Perencanaan': 1, 'Instrumen Asesmen': 2,
            'Keputusan & Rekaman Asesmen': 3, 'Validasi Asesmen': 4, 'Formulir Lainnya': 5}
    entries.sort(key=lambda e: (urut.get(kelompok(e['kode']), 9),
                                re.sub(r'\D', '', e['kode']) or '999', e['kode'], e['file']))
    bagian = {}
    for e in entries:
        bagian.setdefault(kelompok(e['kode']), []).append(e)
    blok = []
    for label, rows in bagian.items():
        kartu = '\n'.join(
            f'<a class="kartu" href="{esc(e["file"])}"><b>{esc(e["kode"])}</b>'
            f'<span>{esc(e["judul"])}</span></a>' for e in rows)
        blok.append(f'<section><h2>{esc(label)}</h2><div class="grid">{kartu}</div></section>')
    tpl = f'''<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Template Formulir Asesmen BNSP</title>
<style>
*{{box-sizing:border-box}}
body{{margin:0;padding:28px 22px 60px;font:14px/1.45 Arial,sans-serif;color:#111;background:#eef0f3}}
header{{max-width:1080px;margin:0 auto 22px}}
h1{{font-size:22px;margin:0 0 4px}}
header p{{margin:0;color:#555}}
#cari{{margin-top:14px;width:100%;max-width:360px;padding:8px 10px;font:inherit;border:1px solid #bbb;border-radius:6px;background:#fff}}
section{{max-width:1080px;margin:0 auto 26px}}
h2{{font-size:14px;text-transform:uppercase;letter-spacing:.06em;color:#666;margin:0 0 10px;border-bottom:1px solid #d3d7dd;padding-bottom:6px}}
.grid{{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:10px}}
.kartu{{display:block;padding:10px 12px;background:#fff;border:1px solid #d8dce3;border-radius:8px;text-decoration:none;color:inherit;transition:.15s}}
.kartu:hover{{border-color:#111;box-shadow:0 2px 8px #0002;transform:translateY(-1px)}}
.kartu b{{display:block;font-size:11px;letter-spacing:.04em;color:#0a6}}
.kartu span{{display:block;font-size:13px;color:#222}}
</style></head>
<body><header><h1>Template Formulir Asesmen BNSP</h1>
<p>{len(entries)} dokumen \u2014 hasil konversi .docx ke HTML (A4, siap cetak / simpan PDF).</p>
<input id="cari" placeholder="Cari formulir\u2026" oninput="saring(this.value)"></header>
{''.join(blok)}
<script>
function saring(q){{q=q.toLowerCase();document.querySelectorAll('.kartu').forEach(function(k){{
 k.style.display=k.textContent.toLowerCase().includes(q)?'':'none';}});
document.querySelectorAll('section').forEach(function(s){{
 var tampil=[].some.call(s.querySelectorAll('.kartu'),function(k){{return k.style.display!=='none';}});
 s.style.display=tampil?'':'none';}});}}
</script>
</body></html>
'''
    with open(os.path.join(OUT, 'index.html'), 'w', encoding='utf-8') as fh:
        fh.write(tpl)


def copy_manual():
    """Template tulis tangan (folder `manual/`) disalin apa adanya ke html/."""
    if not os.path.isdir('manual'):
        return []
    hasil = []
    for f in sorted(os.listdir('manual')):
        if f.lower().endswith('.html'):
            shutil.copyfile(os.path.join('manual', f), os.path.join(OUT, f))
            hasil.append(f)
    return hasil


def main():
    os.makedirs(OUT, exist_ok=True)
    files = [f for f in sorted(os.listdir('.'))
             if f.lower().endswith('.docx') and not f.startswith('~$')]
    manual = {
        'FR.AK.01_Persetujuan_Asesmen_dan_Kerahasiaan.html':
            ('FR.AK.01', 'Persetujuan Asesmen dan Kerahasiaan'),
        'FR.AK.04_Banding_Asesmen.html': ('FR.AK.04', 'Banding Asesmen'),
        'FR.AK.07_Penyesuaian_Wajar_dan_Beralasan.html':
            ('FR.AK.07', 'Ceklis Penyesuaian yang Wajar dan Beralasan'),
        '000_Perangkat_COVER_MUK.html': ('000', 'Perangkat Cover MUK'),
    }
    copy_manual()
    out = [{'file': f, 'kode': k, 'judul': j, 'sumber': ''}
           for f, (k, j) in manual.items()]
    for f in files:
        if f in SKIP:
            continue
        try:
            res = convert(f)
            out.append(res)
            print('ok  ', res['file'])
        except Exception as e:  # noqa: BLE001
            print('FAIL', f, type(e).__name__, e)
    write_index(out)
    with open(os.path.join(OUT, 'index.json'), 'w', encoding='utf-8') as fh:
        json.dump(out, fh, ensure_ascii=False, indent=1)


if __name__ == '__main__':
    main()
