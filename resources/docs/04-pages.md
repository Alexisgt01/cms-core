---
title: "Pages"
icon: "heroicon-o-document-text"
order: 4
---

## 📄 Pages

Le module Pages vous permet de créer et gérer les pages de votre site (accueil, à propos, services, mentions légales, etc.). Chaque page peut contenir des **sections modulaires** que vous assemblez comme des blocs de construction.

Accédez aux pages via le menu **Contenu → Pages**.

---

### ➕ Créer une page

1. Cliquez sur **Créer** en haut à droite
2. Remplissez les informations de base :
   - **Nom** : le titre de la page
   - **Slug** : l'identifiant URL (généré automatiquement à partir du nom)
   - **Clé** : un identifiant technique unique utilisé pour relier la page à un template spécifique sur votre site (votre développeur vous indiquera la clé à utiliser si nécessaire)

> 💡 **Astuce** : Le slug est la partie de l'URL qui identifie votre page. Par exemple, une page nommée « À propos » aura le slug `a-propos` et sera accessible à l'adresse `votresite.com/a-propos`.

---

### 🏠 Page d'accueil

Vous pouvez définir **une seule page** comme page d'accueil de votre site :

1. Éditez la page souhaitée
2. Activez le toggle **Page d'accueil**
3. Enregistrez

> ⚠️ **Important** : Il ne peut y avoir qu'une seule page d'accueil à la fois. Si vous activez cette option sur une nouvelle page, l'ancienne page d'accueil sera automatiquement désactivée.

---

### 🌳 Hiérarchie des pages

Les pages peuvent être organisées de manière **hiérarchique**, avec des pages parentes et des sous-pages :

- **Page parente** : sélectionnez une page parente pour créer une sous-page
- **Position** : définissez l'ordre d'affichage parmi les pages du même niveau

Exemple d'arborescence :

```
📄 Accueil
📄 Services
   ├── 📄 Conseil
   ├── 📄 Formation
   └── 📄 Accompagnement
📄 À propos
📄 Contact
```

> 💡 **Astuce** : La hiérarchie des pages influence les URLs. Une sous-page « Conseil » sous « Services » aura l'URL `votresite.com/services/conseil`.

---

### 📋 Champs méta

Les champs méta sont des **paires clé-valeur** libres que vous pouvez ajouter à chaque page. Ils permettent de stocker des informations complémentaires qui seront utilisées par le template de la page.

Par exemple :
- `couleur_fond` → `#F5F5F5`
- `icone` → `star`
- `sous_titre` → `Découvrez nos services`

> 💡 **Astuce** : Les champs méta sont définis en collaboration avec votre développeur. Ils permettent de personnaliser l'apparence ou le comportement d'une page sans toucher au code.

---

### 📊 États d'une page

Comme les articles de blog, les pages ont des états :

| État | Description |
|------|-------------|
| **Brouillon** 📝 | La page est en cours de création. Elle n'est pas visible sur le site. |
| **Publiée** ✅ | La page est en ligne et accessible aux visiteurs. |

Pour publier une page, utilisez le bouton d'action **Publier** disponible dans la barre d'actions en haut de la page d'édition.

---

### 🧩 Constructeur de sections

Le **constructeur de sections** est l'outil le plus puissant du module Pages. Il vous permet d'assembler votre page à partir de blocs de contenu prédéfinis.

#### Ajouter une section

1. Dans l'onglet **Sections** de votre page, cliquez sur le bouton **Ajouter une section**
2. Une **fenêtre modale** s'ouvre avec le catalogue de toutes les sections disponibles
3. Parcourez les sections ou utilisez la **barre de recherche** pour trouver celle que vous cherchez
4. Cliquez sur la section souhaitée pour l'ajouter à votre page
5. Remplissez les champs de la section (texte, images, liens, etc.)

#### Réorganiser les sections

Vous pouvez **réordonner** vos sections par glisser-déposer :

1. Survolez la section que vous souhaitez déplacer
2. Cliquez sur la **poignée de déplacement** (icône ≡ à gauche)
3. Faites glisser la section vers sa nouvelle position
4. Relâchez

#### Replier / déplier les sections

Pour une meilleure lisibilité lorsque votre page contient beaucoup de sections :

- Cliquez sur le **titre d'une section** pour la replier ou la déplier
- Utilisez les boutons **Tout replier / Tout déplier** pour un contrôle global

#### Enregistrer comme modèle

Si vous avez configuré une section de manière précise et souhaitez la réutiliser :

1. Cliquez sur l'icône **⋮** (menu d'actions) de la section
2. Sélectionnez **Enregistrer comme modèle**
3. Donnez un nom à votre modèle
4. Ce modèle sera ensuite disponible dans le catalogue lors de l'ajout d'une nouvelle section

> 💡 **Astuce** : Les modèles de sections sont un excellent moyen de gagner du temps ! Créez des modèles pour les blocs que vous utilisez souvent (bannière de page, section témoignages, appel à l'action, etc.).

#### Supprimer une section

1. Cliquez sur l'icône **⋮** (menu d'actions) de la section
2. Sélectionnez **Supprimer**
3. Confirmez la suppression

> ⚠️ **Attention** : La suppression d'une section est irréversible une fois la page enregistrée. Tant que vous n'avez pas cliqué sur Enregistrer, vous pouvez annuler en quittant la page sans sauvegarder.

---

### 🌍 Sections globales

Les **sections globales** sont des sections partagées entre plusieurs pages. Contrairement aux modèles qui copient les données, une section globale est une **référence unique** : modifiez-la une seule fois, et le changement s'applique partout où elle est utilisée.

#### Créer une section globale

1. Accédez à **Contenu → Sections globales**
2. Cliquez sur **Créer une section globale**
3. Choisissez le **type de section**
4. Donnez-lui un **nom** explicite (ex : « CTA Contact Footer »)
5. Remplissez les champs de la section
6. Enregistrez

#### Utiliser une section globale dans une page

1. Dans l'onglet **Sections** de votre page, cliquez sur **Ajouter une section**
2. En bas de la fenêtre modale, retrouvez la zone **Sections globales**
3. Cliquez sur la section globale souhaitée pour l'ajouter

La section apparaît avec un **badge vert « Globale »** et ne peut pas être modifiée directement depuis la page. Un lien **Modifier** ouvre la section globale dans un nouvel onglet.

#### Modifier une section globale

Rendez-vous dans **Contenu → Sections globales**, éditez la section souhaitée. En haut du formulaire, un indicateur montre combien de pages utilisent cette section.

> ⚠️ **Attention** : Toute modification d'une section globale impacte immédiatement toutes les pages qui l'utilisent.

#### Convertir une section existante en section globale

1. Cliquez sur l'icône **⋮** (menu d'actions) d'une section existante
2. Sélectionnez **Convertir en section globale**
3. Donnez un nom à la nouvelle section globale
4. La section est créée et remplacée par une référence globale sur la page

> 💡 **Astuce** : Les sections globales sont idéales pour les éléments répétitifs comme les appels à l'action, les formulaires de contact en pied de page, ou les bannières promotionnelles.

---

### 📑 Catalogue de sections

Le **Catalogue de sections** est accessible depuis le menu **Contenu → Catalogue de sections**. Il vous permet de visualiser toutes les sections disponibles sous forme de grille, avec une description de chacune.

C'est une référence utile pour savoir quels types de blocs vous pouvez utiliser dans vos pages.

---

### 📋 Modèles de sections

Les modèles de sections sont accessibles via **Contenu → Modèles de sections**. Vous pouvez y :

- Voir tous les modèles enregistrés
- Créer de nouveaux modèles
- Modifier les modèles existants
- Supprimer les modèles que vous n'utilisez plus

---

### 📑 Dupliquer une page

Pour créer une copie d'une page existante :

1. Ouvrez la page que vous souhaitez dupliquer
2. Cliquez sur le bouton **Dupliquer** dans la barre d'actions
3. Une copie complète sera créée (contenu, sections, SEO, méta), en état **Brouillon**
4. Modifiez le nom et le slug de la copie avant de la publier

> 💡 **Astuce** : La duplication est très pratique pour créer des pages similaires. Par exemple, si vous avez plusieurs pages de services avec la même structure, dupliquez la première et changez simplement le contenu.

---

### 🗑️ Supprimer et restaurer une page

Les pages utilisent la **corbeille** (suppression douce) :

- **Supprimer** une page la place dans la corbeille. Elle n'est plus visible sur le site, mais elle n'est pas définitivement perdue.
- **Restaurer** une page la sort de la corbeille et la remet en brouillon.
- **Supprimer définitivement** efface la page de manière irréversible.

> 💡 **Astuce** : Utilisez le filtre **Corbeille** dans la liste des pages pour retrouver et restaurer une page supprimée par erreur.

---

### 🎯 Bonnes pratiques pour les pages

- **Définissez une page d'accueil** dès le début de la construction de votre site
- **Organisez vos pages en hiérarchie** pour des URLs propres et une navigation logique
- **Utilisez les sections** plutôt que de tout mettre dans un seul bloc de texte
- **Enregistrez des modèles** pour les sections que vous utilisez souvent
- **Remplissez le SEO** de chaque page (voir la section dédiée au SEO)
- **Publiez uniquement** quand le contenu est prêt et relu
