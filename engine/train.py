import csv
import os
import joblib
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, classification_report

dataset_path = os.path.join(os.path.dirname(__file__), "PDFMalware2022.csv")

if not os.path.exists(dataset_path):
    print(f"[-] Error: {dataset_path} not found.")
    exit(1)

X = []
y = []

# Load benchmark dataset using native csv reader
with open(dataset_path, mode="r", encoding="utf-8") as f:
    reader = csv.reader(f)
    headers = next(reader)
    for row in reader:
        if not row:
            continue
        # Features: [PdfSize_KB, ByteEntropy, JS_Count, Launch_Count, OpenAction_Count, EmbeddedFiles_Count]
        features = [float(val) for val in row[:-1]]
        label = int(row[-1])
        X.append(features)
        y.append(label)

print(f"[+] Loaded {len(X)} records from PDFMalware2022.csv")
print(f"[+] Features: {headers[:-1]}")

# 80% Train, 20% Test split
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.20, random_state=42, stratify=y
)

print(f"[+] Training Random Forest Classifier on {len(X_train)} samples...")
clf = RandomForestClassifier(
    n_estimators=100,
    max_depth=10,
    min_samples_split=4,
    random_state=42,
    n_jobs=-1
)

clf.fit(X_train, y_train)

# Evaluation on the unseen 20% test set
y_pred = clf.predict(X_test)
accuracy = accuracy_score(y_test, y_pred)

print(f"\n================ MODEL EVALUATION METRICS ================")
print(f"Algorithm: Random Forest Ensemble (100 Trees)")
print(f"Accuracy : {accuracy * 100:.2f}%\n")
print(classification_report(y_test, y_pred, target_names=["Benign (0)", "Malicious (1)"]))
print("==========================================================\n")

# Export trained model
model_output = os.path.join(os.path.dirname(__file__), "threat_model.pkl")
joblib.dump(clf, model_output)
print(f"[✓] Model artifact successfully compiled and saved: {model_output}")
