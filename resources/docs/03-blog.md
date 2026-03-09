---
title: "Blog"
icon: "heroicon-o-newspaper"
order: 3
---

## 📝 Blog

Le module Blog est le cœur éditorial de votre site. Il vous permet de créer, organiser et publier des articles de blog, de gérer vos auteurs, catégories et tags, et de configurer les paramètres de votre blog.

---

### 📰 Articles

Les articles sont les contenus principaux de votre blog. Vous les trouverez dans le menu **Blog → Articles**.

#### Créer un article

1. Cliquez sur **Créer** en haut à droite de la liste des articles
2. Remplissez les champs suivants :
   - **Titre** : le titre de votre article (il sera aussi utilisé pour générer le slug/URL)
   - **Slug** : l'identifiant URL de l'article (généré automatiquement à partir du titre, modifiable)
   - **Contenu** : rédigez votre article dans l'éditeur enrichi (voir ci-dessous)
   - **Image mise en avant** : l'image principale qui représentera votre article
   - **Extrait** : un court résumé de l'article (utilisé dans les listes et le partage)
   - **Auteur** : sélectionnez l'auteur de l'article
   - **Catégories** : choisissez une ou plusieurs catégories
   - **Tags** : ajoutez des tags pour mieux référencer votre article

#### L'éditeur de contenu (Tiptap)

L'éditeur enrichi vous permet de formater votre texte sans écrire de code :

- **Mise en forme** : gras, italique, souligné, barré
- **Titres** : niveaux H2, H3, H4 pour structurer votre contenu
- **Listes** : à puces ou numérotées
- **Liens** : insérez des liens vers d'autres pages ou sites
- **Images** : insérez des images depuis votre bibliothèque de médias
- **Vidéos** : intégrez des vidéos YouTube ou Vimeo
- **Citations** : mettez en forme des citations
- **Tableaux** : créez des tableaux de données
- **Blocs de code** : pour du contenu technique

> 💡 **Astuce** : Pour insérer une image depuis votre bibliothèque de médias, utilisez le bouton image dans la barre d'outils de l'éditeur. Vous pourrez parcourir vos médias existants ou en ajouter de nouveaux.

#### Les états d'un article

Chaque article a un **état** qui détermine s'il est visible ou non sur votre site :

| État | Icône | Description |
|------|-------|-------------|
| **Brouillon** | 📝 | L'article est en cours de rédaction. Il n'est pas visible sur le site. |
| **Programmé** | 📅 | L'article sera publié automatiquement à la date et l'heure que vous avez choisies. |
| **Publié** | ✅ | L'article est en ligne et visible par tous les visiteurs. |

#### Programmer un article

Pour programmer la publication d'un article :

1. Créez ou éditez votre article
2. Remplissez le champ **Date de publication** avec la date et l'heure souhaitées (dans le futur)
3. L'article passera en état **Programmé**
4. À la date prévue, il sera automatiquement publié

> 💡 **Astuce** : La programmation d'articles est idéale pour préparer du contenu à l'avance et maintenir un rythme de publication régulier, même pendant vos vacances !

---

### ✍️ Auteurs

Les auteurs sont les personnes qui rédigent les articles. Accédez-y via **Blog → Auteurs**.

#### Créer un auteur

1. Cliquez sur **Créer**
2. Remplissez les informations :
   - **Nom** : le nom affiché publiquement
   - **Compte utilisateur** : liez l'auteur à un compte utilisateur existant (optionnel)
   - **Avatar** : ajoutez une photo de profil
   - **Biographie** : une courte présentation de l'auteur
   - **Réseaux sociaux** : ajoutez les liens vers les profils sociaux (Twitter/X, LinkedIn, site web, etc.)

> 💡 **Astuce** : Lier un auteur à un compte utilisateur permet d'identifier automatiquement qui rédige quoi. Cela n'est pas obligatoire si vous gérez les auteurs indépendamment des comptes.

---

### 📂 Catégories

Les catégories permettent d'organiser vos articles par thématique. Elles sont **hiérarchiques** : une catégorie peut avoir des sous-catégories.

#### Organisation hiérarchique

Vous pouvez créer une arborescence de catégories. Par exemple :

```
🏠 Immobilier
   ├── 🏢 Appartements
   ├── 🏡 Maisons
   └── 📊 Marché immobilier
🌍 Voyage
   ├── 🇫🇷 France
   └── 🌎 International
```

Pour créer une sous-catégorie, sélectionnez simplement une **catégorie parente** lors de la création.

#### Champs disponibles

- **Nom** : le nom de la catégorie
- **Slug** : l'identifiant URL (généré automatiquement)
- **Description** : une description de la catégorie (utile pour le SEO)
- **Catégorie parente** : pour créer une sous-catégorie
- **Image** : une image représentative de la catégorie

---

### 🏷️ Tags

Les tags (étiquettes) permettent de créer des liens transversaux entre vos articles. Contrairement aux catégories, les tags ne sont pas hiérarchiques : ce sont de simples mots-clés.

Un article peut avoir **plusieurs tags**, et un même tag peut être associé à **plusieurs articles**.

#### Création rapide depuis un article

Vous n'avez pas besoin d'aller dans la section Tags pour en créer un nouveau ! Lorsque vous éditez un article :

1. Cliquez dans le champ **Tags**
2. Tapez le nom du tag souhaité
3. S'il n'existe pas encore, vous pouvez le **créer directement** depuis le formulaire

> 💡 **Astuce** : Utilisez les tags pour des sujets transversaux qui traversent plusieurs catégories. Par exemple, le tag « Tendances 2026 » peut apparaître aussi bien dans la catégorie « Immobilier » que « Voyage ».

---

### ⚙️ Réglages du blog

Accédez aux réglages via **Blog → Réglages du blog**. Vous y trouverez plusieurs onglets :

#### Général

- **Titre du blog** : le nom de votre blog
- **Description** : la description générale du blog
- **Nombre d'articles par page** : combien d'articles afficher dans les listings

#### Flux RSS

- **Activer le flux RSS** : permet aux lecteurs de s'abonner à votre blog via un lecteur RSS
- **Nombre d'articles dans le flux** : combien d'articles inclure dans le flux RSS

#### Images

- **Taille par défaut des images** : définissez les dimensions par défaut pour les images mises en avant
- **Format d'image** : choisissez le format de compression (WebP recommandé pour de meilleures performances)

#### SEO par défaut

- **Titre SEO par défaut** : le modèle de titre utilisé pour les articles sans titre SEO personnalisé
- **Description SEO par défaut** : la description utilisée en l'absence de description personnalisée

> 💡 **Astuce** : Configurez bien vos réglages SEO par défaut dès le départ. Ainsi, même si vous oubliez de remplir le SEO d'un article, il aura quand même des métadonnées correctes.

---

### 🎯 Bonnes pratiques pour le blog

- **Structurez vos articles** avec des titres (H2, H3) pour améliorer la lisibilité et le SEO
- **Ajoutez toujours une image mise en avant** : elle apparaîtra dans les listes et le partage sur les réseaux sociaux
- **Remplissez l'extrait** : il sera utilisé dans les aperçus et les résultats de recherche
- **Utilisez les catégories pour la structure principale** et les tags pour les mots-clés secondaires
- **Programmez vos articles** pour maintenir une fréquence de publication régulière
- **Relisez le SEO** avant de publier (voir la section dédiée au SEO)
