#!/usr/bin/env python
# -*- coding: utf-8 -*-

import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score
import xgboost as xgb
import pickle
import os

print("="*60)
print("ENTRAINNEMENT DU MODELE DE PREDICTION D'ABSENCES")
print("="*60)

# ETAPE 1: GENERER LES DONNEES

print("\n[1/6] Generation des donnees d'entrainnement...")

n_samples = 500
data = {
    'employee_id': np.random.randint(1, 101, n_samples),
    'nb_absences_prev': np.random.randint(0, 20, n_samples),
    'nb_retards_prev': np.random.randint(0, 15, n_samples),
    'anciennete_mois': np.random.randint(1, 180, n_samples),
    'jour_semaine': np.random.randint(0, 7, n_samples),
    'est_lundi': np.random.randint(0, 2, n_samples),
    'est_avant_vacances': np.random.randint(0, 2, n_samples),
    'temperature_exterieure': np.random.uniform(-10, 35, n_samples),
    'nb_pointages_correctes_prev': np.random.randint(0, 22, n_samples),
}

df = pd.DataFrame(data)

# Creer la cible
absent_probability = (
    (df['nb_absences_prev'] / 20) * 0.4 +
    (df['est_lundi'] * 0.2) +
    (df['est_avant_vacances'] * 0.2) +
    (1 - df['anciennete_mois'] / 180) * 0.2
)

absent_probability += np.random.normal(0, 0.1, n_samples)
absent_probability = np.clip(absent_probability, 0, 1)
df['absent_demain'] = (absent_probability > 0.5).astype(int)

# Sauvegarde
os.makedirs('data', exist_ok=True)
df.to_csv('data/training_data.csv', index=False)
print(f"OK - {n_samples} echantillons generes")
print(f"    Absences: {df['absent_demain'].sum()} ({df['absent_demain'].mean()*100:.1f}%)")

# ETAPE 2: PREPARER LES DONNEES

print("\n[2/6] Preparation des donnees...")

X = df.drop('absent_demain', axis=1)
y = df['absent_demain']

X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test)

print(f"OK - Train: {len(X_train)} | Test: {len(X_test)}")
feature_names = X.columns.tolist()

# ETAPE 3: ENTRAINER LE MODELE

print("\n[3/6] Entrainnement du modele XGBoost...")

model = xgb.XGBClassifier(
    n_estimators=100,
    max_depth=6,
    learning_rate=0.1,
    subsample=0.8,
    colsample_bytree=0.8,
    random_state=42,
    eval_metric='logloss',
    use_label_encoder=False,
    verbosity=0
)

model.fit(X_train_scaled, y_train, verbose=False)
print("OK - Modele entrainnee")

# ETAPE 4: EVALUER LE MODELE

print("\n[4/6] Evaluation du modele...")

y_pred = model.predict(X_test_scaled)

accuracy = accuracy_score(y_test, y_pred)
precision = precision_score(y_test, y_pred)
recall = recall_score(y_test, y_pred)
f1 = f1_score(y_test, y_pred)

print(f"OK")
print(f"    Accuracy:  {accuracy*100:.2f}%")
print(f"    Precision: {precision*100:.2f}%")
print(f"    Recall:    {recall*100:.2f}%")
print(f"    F1-Score:  {f1*100:.2f}%")

# ETAPE 5: FEATURE IMPORTANCE

print("\n[5/6] Importance des features...")

feature_importance = model.feature_importances_
for feat, imp in sorted(zip(feature_names, feature_importance), 
                        key=lambda x: x[1], reverse=True)[:5]:
    print(f"    {feat}: {imp*100:.2f}%")

# ETAPE 6: SAUVEGARDER

print("\n[6/6] Sauvegarde du modele...")

os.makedirs('models', exist_ok=True)

with open('models/absence_model.pkl', 'wb') as f:
    pickle.dump(model, f)

with open('models/scaler.pkl', 'wb') as f:
    pickle.dump(scaler, f)

with open('models/feature_names.pkl', 'wb') as f:
    pickle.dump(feature_names, f)

print("OK - Modele sauvegarde dans models/absence_model.pkl")

print("\n" + "="*60)
print("ENTRAINNEMENT TERMINE!")
print("="*60)