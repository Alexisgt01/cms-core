---
title: "Contact"
icon: "heroicon-o-envelope"
order: 9
---

## ✉️ Contact

Le module Contact gère tout ce qui concerne les **formulaires de contact** de votre site : les messages reçus, les coordonnées des contacts et les intégrations avec des services externes (webhooks).

Accédez à cette section via le menu **Contact**.

---

### 👥 Contacts

La liste des **Contacts** regroupe toutes les personnes qui vous ont contacté via vos formulaires. Accédez-y via **Contact → Contacts**.

Chaque contact est créé automatiquement lors de la première soumission d'un formulaire. Si la même personne vous contacte plusieurs fois (identifiée par son adresse e-mail), ses demandes sont regroupées sous un seul contact.

#### Informations d'un contact

- **Nom** : le nom de la personne
- **E-mail** : son adresse e-mail
- **Téléphone** : son numéro de téléphone (si fourni)
- **Nombre de demandes** : combien de fois cette personne vous a contacté
- **Date du premier contact** : quand elle vous a contacté pour la première fois
- **Date du dernier contact** : sa demande la plus récente

> 💡 **Astuce** : La liste des contacts est un mini-CRM intégré. Utilisez-la pour retrouver rapidement les coordonnées d'une personne qui vous a contacté.

---

### 📥 Demandes de contact

Les **Demandes de contact** sont l'historique de tous les messages reçus via vos formulaires. C'est votre **boîte de réception**. Accédez-y via **Contact → Demandes**.

#### États d'une demande

Chaque demande possède un état pour suivre son traitement :

| État | Icône | Description |
|------|-------|-------------|
| **Nouvelle** | 🔵 | La demande vient d'arriver et n'a pas encore été traitée |
| **En cours de traitement** | 🟡 | Vous avez pris connaissance de la demande et y travaillez |
| **Archivée** | ⚪ | La demande a été traitée et archivée |

#### Traiter une demande

1. Ouvrez la demande en cliquant dessus
2. Lisez le message et les informations du contact
3. Changez l'état selon l'avancement :
   - Passez en **En cours de traitement** quand vous commencez à vous en occuper
   - Passez en **Archivée** quand c'est résolu
4. Répondez au contact par e-mail (depuis votre messagerie habituelle)

> 💡 **Astuce** : Traitez vos nouvelles demandes régulièrement ! Un temps de réponse court est essentiel pour la satisfaction de vos contacts. Consultez les demandes « Nouvelles » chaque jour.

---

### 🔗 Webhooks

Les **webhooks** permettent d'envoyer automatiquement les données de vos formulaires de contact vers des **services externes** (CRM, outil de marketing, Slack, etc.).

Accédez-y via **Contact → Webhooks**.

#### Qu'est-ce qu'un webhook ?

Imaginez un webhook comme un **pont automatique** : chaque fois qu'un formulaire est soumis, les données sont automatiquement envoyées vers un service externe que vous avez configuré.

Exemples d'utilisation :
- Envoyer les contacts vers votre CRM (HubSpot, Salesforce, etc.)
- Recevoir une notification Slack quand quelqu'un vous contacte
- Ajouter le contact à votre liste de newsletter (Mailchimp, Sendinblue, etc.)

#### Créer un webhook

1. Cliquez sur **Créer**
2. Remplissez les champs :
   - **Nom** : un nom descriptif (ex : « Envoi vers HubSpot »)
   - **URL** : l'adresse du service externe fournie par celui-ci
   - **Activé** : activez ou désactivez le webhook
3. Enregistrez

> 💡 **Astuce** : La configuration des webhooks nécessite généralement l'aide de votre développeur ou du service externe. Ils vous fourniront l'URL à renseigner.

#### Suivi des envois

Chaque webhook dispose d'un historique d'envoi qui vous permet de voir :

- Si l'envoi a **réussi** ou **échoué**
- La **date et l'heure** de l'envoi
- Le **code de réponse** du service externe

#### Relancer un webhook en échec

Si un envoi a échoué (service externe temporairement indisponible, par exemple), vous pouvez le **relancer** :

1. Accédez à l'historique du webhook
2. Cliquez sur le bouton **Relancer** à côté de l'envoi en échec
3. Le système tentera un nouvel envoi

> 💡 **Astuce** : Les webhooks intègrent un système de **tentatives automatiques** avec des délais croissants entre chaque tentative. Un échec ponctuel sera souvent résolu automatiquement.

---

### ⚙️ Réglages du contact

Les réglages du module contact sont accessibles via **Contact → Réglages**.

#### Options disponibles

- **Mode asynchrone** : lorsqu'activé, les formulaires de contact sont traités en arrière-plan. Cela accélère la soumission du formulaire pour le visiteur.
- **Durée de rétention** : combien de temps conserver les demandes archivées avant suppression automatique
- **Secret entrant** : une clé de sécurité pour authentifier les soumissions de formulaires (configurée par votre développeur)

> ⚠️ **Attention** : Ne modifiez pas le secret entrant sans en parler à votre développeur, car cela pourrait empêcher vos formulaires de fonctionner.

---

### 🎯 Bonnes pratiques pour le module Contact

- **Consultez vos demandes quotidiennement** pour répondre rapidement à vos contacts
- **Utilisez les états** pour suivre l'avancement du traitement de chaque demande
- **Archivez** les demandes traitées pour garder une boîte de réception propre
- **Vérifiez les webhooks** régulièrement pour vous assurer que les intégrations fonctionnent
- **Relancez les webhooks en échec** si vous constatez des envois manqués
- **Ne supprimez pas les contacts** : ils constituent votre base de données de prospects
