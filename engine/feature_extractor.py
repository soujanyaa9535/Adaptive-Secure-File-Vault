import os
import math
import re
import zipfile
import numpy as np

FEATURE_NAMES = [
    "file_size_kb",
    "byte_entropy",
    "is_pdf",
    "pdf_js_count",
    "pdf_launch_count",
    "pdf_openaction_count",
    "pdf_embedded_count",
    "is_docx",
    "docx_has_vba",
    "docx_has_ole",
    "docx_ext_template",
    "is_image",
    "img_trailing_bytes",
    "img_script_tag",
    "sqli_pattern_count",
    "rce_pattern_count"
]

def calculate_entropy(data: bytes) -> float:
    if not data:
        return 0.0
    length = len(data)
    byte_counts = [0] * 256
    for b in data:
        byte_counts[b] += 1
    entropy = 0.0
    for count in byte_counts:
        if count > 0:
            p = count / length
            entropy -= p * math.log2(p)
    return round(entropy, 4)

def extract_features(file_path: str) -> np.ndarray:
    if not os.path.exists(file_path):
        return np.zeros(len(FEATURE_NAMES))

    with open(file_path, "rb") as f:
        content = f.read()

    ext = os.path.splitext(file_path)[1].lower().replace(".", "")
    size_kb = len(content) / 1024.0
    entropy = calculate_entropy(content)

    is_pdf = 1.0 if ext == "pdf" else 0.0
    pdf_js = 0.0
    pdf_launch = 0.0
    pdf_openaction = 0.0
    pdf_embedded = 0.0

    is_docx = 1.0 if ext in ["docx", "doc"] else 0.0
    docx_vba = 0.0
    docx_ole = 0.0
    docx_ext_template = 0.0

    is_image = 1.0 if ext in ["png", "jpg", "jpeg"] else 0.0
    img_trailing = 0.0
    img_script = 0.0

    sqli_count = 0.0
    rce_count = 0.0

    if is_pdf:
        pdf_js = float(content.count(b"/JavaScript") + content.count(b"/JS"))
        pdf_launch = float(content.count(b"/Launch"))
        pdf_openaction = float(content.count(b"/OpenAction") + content.count(b"/AA"))
        pdf_embedded = float(content.count(b"/EmbeddedFiles"))

    if is_docx:
        try:
            with zipfile.ZipFile(file_path, "r") as z:
                names = z.namelist()
                for name in names:
                    if "vbaProject.bin" in name or name.endswith(".vba"):
                        docx_vba = 1.0
                    if "oleObject" in name:
                        docx_ole = 1.0
                    if name.endswith(".rels"):
                        rel_data = z.read(name).decode("utf-8", errors="ignore")
                        if 'TargetMode="External"' in rel_data:
                            docx_ext_template = 1.0
        except Exception:
            pass

    if is_image:
        if ext == "png":
            idx = content.find(b"IEND")
            if idx != -1 and len(content) > (idx + 8):
                img_trailing = float(len(content) - (idx + 8))
        elif ext in ["jpg", "jpeg"]:
            idx = content.find(b"\xff\xd9")
            if idx != -1 and len(content) > (idx + 2):
                img_trailing = float(len(content) - (idx + 2))

        for pattern in [b"<?php", b"<script", b"eval(", b"base64_decode("]:
            if pattern in content:
                img_script = 1.0
                break

    text_data = content.decode("utf-8", errors="ignore")
    sqli_regex = r"(\bUNION\b\s+\bSELECT\b|'\s+OR\s+'1'='1|;\s*DROP\s+TABLE|--\s*$)"
    rce_regex = r"(\/bin\/(ba)?sh|powershell(\.exe)?|cmd\.exe|eval\(|base64_decode\()"

    sqli_count = float(len(re.findall(sqli_regex, text_data, re.IGNORECASE)))
    rce_count = float(len(re.findall(rce_regex, text_data, re.IGNORECASE)))

    features = [
        size_kb,
        entropy,
        is_pdf,
        pdf_js,
        pdf_launch,
        pdf_openaction,
        pdf_embedded,
        is_docx,
        docx_vba,
        docx_ole,
        docx_ext_template,
        is_image,
        img_trailing,
        img_script,
        sqli_count,
        rce_count
    ]

    return np.array(features, dtype=np.float32)
