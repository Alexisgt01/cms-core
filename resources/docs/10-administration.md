---
title: "Administration"
icon: "heroicon-o-cog-6-tooth"
order: 10
---

## ⚙️ Administration

La section Administration regroupe tous les outils de gestion avancée de votre site : utilisateurs, rôles, journal d'activité, réglages du site et accès restreint.

Accédez à cette section via le menu **Administration**.

---

### 👥 Gestion des utilisateurs

Les utilisateurs sont les personnes qui ont accès à votre panneau d'administration. Accédez-y via **Administration → Utilisateurs**.

#### Créer un utilisateur

1. Cliquez sur **Créer**
2. Remplissez les champs :
   - **Nom** : le nom complet de l'utilisateur
   - **E-mail** : son adresse e-mail (sera utilisée pour la connexion)
   - **Mot de passe** : définissez un mot de passe initial
   - **Rôle(s)** : attribuez un ou plusieurs rôles
3. Cliquez sur **Créer**

> 💡 **Astuce** : Communiquez le mot de passe initial de manière sécurisée (en personne ou via un canal chiffré). Demandez à l'utilisateur de le changer à sa première connexion.

#### Modifier un utilisateur

1. Cliquez sur l'utilisateur dans la liste
2. Modifiez les informations souhaitées
3. Pour changer le mot de passe, remplissez le champ dédié (laissez-le vide pour conserver l'actuel)
4. Enregistrez

#### Désactiver un accès

Pour empêcher un utilisateur d'accéder au panneau d'administration, vous pouvez :

- **Modifier ses rôles** pour lui retirer ses droits
- **Supprimer son compte** si l'accès n'est plus nécessaire

> ⚠️ **Important** : Ne supprimez pas votre propre compte ! Et assurez-vous qu'il reste toujours au moins un administrateur actif.

---

### 🛡️ Rôles et permissions

Les rôles déterminent **ce que chaque utilisateur peut faire** dans le panneau d'administration. Accédez-y via **Administration → Rôles**.

#### Les rôles par défaut

Votre CMS est livré avec trois rôles pré-configurés :

| Rôle | Description | Exemples de droits |
|------|-------------|-------------------|
| **Super Admin** | Accès complet à tout | Tout voir, créer, modifier, supprimer + réglages du site |
| **Éditeur** | Gestion du contenu | Articles, pages, médias, catégories, tags (sans suppression), consulter les contacts |
| **Lecteur** | Accès en lecture seule | Voir les utilisateurs et les rôles uniquement |

#### Détail des permissions de l'éditeur

L'éditeur peut :
- ✅ Créer, modifier et publier des articles, pages et collections
- ✅ Gérer les médias (importer, modifier, organiser)
- ✅ Créer et modifier des catégories, tags et auteurs
- ✅ Créer et modifier des redirections
- ✅ Consulter les contacts, demandes et webhooks
- ✅ Voir le journal d'activité
- ❌ Supprimer des articles, pages, catégories, tags, auteurs, redirections ou collections
- ❌ Modifier les réglages du site
- ❌ Gérer les utilisateurs (création/modification)
- ❌ Gérer les réglages de contact et les webhooks

> 💡 **Astuce** : Le rôle **Éditeur** est idéal pour les rédacteurs et contributeurs réguliers. Il leur donne accès à tout ce dont ils ont besoin pour créer du contenu, sans risque de modifier les réglages du site ou de supprimer des contenus importants.

---

### 📋 Journal d'activité

Le journal d'activité enregistre automatiquement **toutes les actions** effectuées dans le panneau d'administration. Accédez-y via **Administration → Journal d'activité**.

#### Ce qui est enregistré

Le journal trace les actions sur les éléments suivants :

- 📝 **Articles de blog** : création, modification, publication, suppression
- 📂 **Catégories** : création, modification, suppression
- 🏷️ **Tags** : création, modification, suppression
- ✍️ **Auteurs** : création, modification, suppression
- ↪️ **Redirections** : création, modification, suppression
- 📄 **Pages** : création, modification, publication, suppression

#### Informations disponibles

Pour chaque action, le journal affiche :

- **Qui** : l'utilisateur qui a effectué l'action
- **Quoi** : le type d'action (créé, modifié, supprimé)
- **Sur quoi** : l'élément concerné
- **Quand** : la date et l'heure exactes
- **Détails** : les champs modifiés (valeurs avant/après)

> 💡 **Astuce** : Le journal d'activité est un outil précieux pour comprendre ce qui a changé et quand. Si un contenu a été modifié de manière inattendue, consultez le journal pour savoir qui a fait la modification et ce qui a été changé.

> ⚠️ **Bon à savoir** : Le journal d'activité est en **lecture seule**. Vous pouvez le consulter mais pas le modifier.

---

### 🏢 Réglages du site

Les réglages du site centralisent toute la configuration générale de votre site. Accédez-y via **Administration → Réglages du site**.

Les réglages sont organisés en plusieurs onglets :

#### 🏷️ Identité

Les informations de base de votre site :

- **Nom du site** : le nom qui apparaîtra dans le navigateur et les métadonnées
- **Description** : une courte description de votre site
- **Logo** : le logo principal
- **Favicon** : la petite icône affichée dans l'onglet du navigateur

#### 📞 Contact

Les coordonnées de votre entreprise :

- **E-mail de contact** : l'adresse e-mail principale
- **Téléphone** : le numéro de téléphone
- **Adresse** : l'adresse postale

#### 🔒 Accès restreint

Protégez votre site par un mot de passe (voir section dédiée ci-dessous).

#### 🔍 SEO global

Les paramètres SEO par défaut appliqués à l'ensemble du site :

- **Titre par défaut** : le titre utilisé quand aucun titre spécifique n'est défini
- **Description par défaut** : la description par défaut
- **Image OG par défaut** : l'image de partage par défaut
- **Scripts d'en-tête** : code à injecter dans le `<head>` (Google Analytics, etc.)

> 💡 **Astuce** : Configurez bien le SEO global dès le départ. Ces valeurs seront utilisées comme **solution de repli** quand un contenu n'a pas de SEO personnalisé.

#### ⚖️ Informations légales

- **Mentions légales** : contenu des mentions légales
- **Politique de confidentialité** : contenu de la politique de confidentialité

#### 🌐 Réseaux sociaux

Les liens vers vos profils sur les réseaux sociaux :

- Facebook, Instagram, X (Twitter), LinkedIn, YouTube, TikTok, Pinterest, etc.

> 💡 **Astuce** : Renseignez vos réseaux sociaux ici pour qu'ils apparaissent automatiquement dans le pied de page et les métadonnées de votre site.

#### 🎛️ Fonctionnalités

L'onglet Fonctionnalités vous permet d'**activer ou désactiver les modules** visibles dans la barre latérale. Cela permet de simplifier l'interface en masquant les modules que vous n'utilisez pas.

- Chaque **groupe** (Blog, Contact, Pages...) peut être désactivé entièrement
- Chaque **élément** au sein d'un groupe peut être masqué individuellement (ex : cacher les Webhooks tout en gardant le reste du module Contact)
- Les modules désactivés sont **masqués et inaccessibles** pour tous les utilisateurs
- Les modifications prennent effet après sauvegarde et rechargement de la page

> 💡 **Astuce** : Si votre site n'utilise pas le blog, désactivez simplement le module Blog pour alléger la barre latérale. Vous pourrez toujours le réactiver plus tard.

> ⚠️ **Attention** : Si vous désactivez « Paramètres du site », vous ne pourrez plus accéder à cette page. Un administrateur technique devra réactiver la fonctionnalité en base de données.

#### 🔧 Admin

Paramètres avancés de l'administration :

- **Afficher la version dans le pied de page** : affiche le numéro de version du CMS en bas du panneau d'administration

---

### 🔒 Accès restreint

L'accès restreint permet de **protéger l'ensemble de votre site public par un mot de passe**. C'est utile pendant la construction du site ou pour un site privé.

#### Activer l'accès restreint

1. Allez dans **Administration → Réglages du site**
2. Ouvrez l'onglet **Accès restreint**
3. Activez l'option **Accès restreint**
4. Définissez un **mot de passe**
5. Configurez la **durée de validité** du cookie (combien de temps un visiteur reste connecté après avoir entré le mot de passe)
6. Enregistrez

#### Comment ça fonctionne

- Les visiteurs qui accèdent à votre site voient une **page de connexion** leur demandant le mot de passe
- Une fois le bon mot de passe entré, un **cookie** est enregistré et ils peuvent naviguer librement
- Le cookie expire après la durée configurée, après quoi ils devront ressaisir le mot de passe
- Les **administrateurs connectés** au panneau d'administration ne sont jamais bloqués

> 💡 **Astuce** : L'accès restreint est parfait pour protéger votre site pendant sa construction. N'oubliez pas de le **désactiver** avant le lancement officiel !

> ⚠️ **Important** : Communiquez le mot de passe uniquement aux personnes autorisées. Changez-le régulièrement si de nombreuses personnes le connaissent.

---

### 🎯 Bonnes pratiques d'administration

- **Créez un compte par utilisateur** : ne partagez jamais un même compte entre plusieurs personnes
- **Attribuez le bon rôle** : donnez à chaque utilisateur uniquement les droits dont il a besoin
- **Consultez le journal d'activité** régulièrement pour surveiller les modifications
- **Configurez les réglages du site** en premier** (identité, contact, SEO global, réseaux sociaux)
- **Gardez au moins deux comptes Super Admin** pour éviter de perdre l'accès
- **Changez les mots de passe** régulièrement et choisissez des mots de passe robustes
- **Désactivez l'accès restreint** avant le lancement de votre site
