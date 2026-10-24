import sys
import os
import json
import math
import joblib

def calculate_entropy(data: bytes) -> float:
    if not data:
        return 0.0
    entropy = 0.0
    length = len(data)
    byte_counts = [0] * 256
    for b in data:
        byte_counts[b] += 1
    for count in byte_counts:
        if count > 0:
            p = count / length
            entropy -= p * math.log2(p)
    return round(entropy, 4)

def analyze_file(file_path: str) -> dict:
    if not os.path.exists(file_path):
        return {"error": "File not found", "is_safe": False}

    with open(file_path, "rb") as f:
        content = f.read()

    file_size = len(content)
    size_kb = file_size / 1024.0
    entropy = calculate_entropy(content)
    anomalies = []

    # 1. Structural PDF Feature Extraction
    js_count = float(content.count(b"/JavaScript") + content.count(b"/JS"))
    launch_count = float(content.count(b"/Launch"))
    openaction_count = float(content.count(b"/OpenAction") + content.count(b"/AA"))
    embedded_count = float(content.count(b"/EmbeddedFiles"))

    features = [size_kb, entropy, js_count, launch_count, openaction_count, embedded_count]

    # 2. Machine Learning Evaluation
    model_path = os.path.join(os.path.dirname(__file__), "threat_model.pkl")
    ml_probability = 0.0

    if os.path.exists(model_path):
        try:
            clf = joblib.load(model_path)
            probs = clf.predict_proba([features])[0]
            ml_probability = round(float(probs[1]) * 100, 2)
        except Exception as e:
            anomalies.append(f"Model evaluation warning: {str(e)}")

    # 3. Context-Aware Threat Evaluation
    # Active execution triggers
    if js_count > 0:
        anomalies.append(f"Embedded JavaScript execution blocks detected ({int(js_count)})")
    
    if launch_count > 0:
        anomalies.append("Contains /Launch action (capable of spawning OS shell/processes)")

    # /OpenAction is only hostile if chained to an execution trigger
    if openaction_count > 0 and (js_count > 0 or launch_count > 0):
        anomalies.append("Weaponized /OpenAction chained to script/process trigger")

    if embedded_count > 0:
        anomalies.append("Hidden embedded file attachments detected inside stream")

    # Binary web-shell tags
    if b"<?php" in content or b"<script" in content.lower():
        anomalies.append("Active executable script payload detected in document stream")

    # High entropy is only an anomaly if combined with an execution vector or non-PDF binary
    has_active_execution = (js_count > 0 or launch_count > 0 or b"<?php" in content)
    if entropy > 7.5 and has_active_execution:
        anomalies.append(f"High Byte Entropy ({entropy}/8.0) paired with executable triggers")

    # 4. Final Risk Scoring
    if not has_active_execution:
        # Benign compressed documents (scans, images) capped at low baseline
        final_score = int(min(ml_probability, 20))
    else:
        final_score = int(max(ml_probability, len(anomalies) * 35))

    final_score = min(100, max(0, final_score))

    if final_score >= 70:
        verdict = "CRITICAL_THREAT"
    elif final_score >= 35:
        verdict = "SUSPICIOUS"
    else:
        verdict = "CLEAN"

    return {
        "file_path": file_path,
        "is_safe": verdict == "CLEAN",
        "verdict": verdict,
        "threat_score": final_score,
        "ml_probability": ml_probability,
        "entropy": entropy,
        "anomalies": anomalies,
        "features": {
            "size_kb": round(size_kb, 2),
            "entropy": entropy,
            "js_count": int(js_count),
            "launch_count": int(launch_count),
            "openaction_count": int(openaction_count),
            "embedded_count": int(embedded_count)
        }
    }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No file path provided"}))
        sys.exit(1)
    
    result = analyze_file(sys.argv[1])
    print(json.dumps(result))
