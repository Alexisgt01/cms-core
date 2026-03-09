---
title: "SEO"
icon: "heroicon-o-magnifying-glass"
order: 6
---

## 🔍 SEO (Référencement naturel)

Le SEO (Search Engine Optimization) est l'ensemble des techniques qui permettent à votre site d'apparaître dans les premiers résultats de Google et des autres moteurs de recherche. Votre CMS intègre des outils puissants pour optimiser chaque contenu.

Les champs SEO sont disponibles sur **tous les types de contenus** : articles de blog, pages, catégories, tags, auteurs et collections.

---

### 📝 Les champs SEO essentiels

Lorsque vous éditez un contenu, vous trouverez un onglet **SEO** contenant les champs suivants :

#### Titre SEO (Meta Title)

C'est le titre qui apparaît dans les résultats de recherche Google. Il doit :

- Contenir **entre 50 et 60 caractères** (au-delà, il sera tronqué)
- Inclure votre **mot-clé principal**
- Être **accrocheur** pour donner envie de cliquer

> 💡 **Astuce** : Le compteur de caractères sous le champ vous aide à rester dans la bonne longueur. Visez la zone verte !

#### Meta Description

C'est le texte descriptif qui apparaît sous le titre dans Google. Elle doit :

- Contenir **entre 120 et 160 caractères**
- Résumer le contenu de la page de manière attractive
- Inclure un **appel à l'action** (« Découvrez... », « Apprenez... »)

#### Titre H1

C'est le titre principal affiché sur la page elle-même (pas dans Google). Il doit :

- Être unique sur la page
- Contenir votre mot-clé principal
- Être différent du titre SEO si possible (pour couvrir plus de variantes)

#### Mot-clé principal (Focus Keyword)

Le mot ou l'expression sur lequel vous souhaitez que cette page soit trouvée dans Google. Par exemple : « location appartement Paris ».

#### Mots-clés secondaires

Des expressions complémentaires liées à votre sujet. Par exemple, si votre mot-clé principal est « location appartement Paris », vos mots-clés secondaires pourraient être : « louer un appartement à Paris », « location meublée Paris ».

#### Contenu SEO (haut et bas de page)

Deux zones de texte enrichi vous permettent d'ajouter du contenu optimisé SEO :

- **Contenu SEO haut** : affiché avant le contenu principal
- **Contenu SEO bas** : affiché après le contenu principal

> 💡 **Astuce** : Ces zones sont idéales pour ajouter du texte riche en mots-clés sur des pages comme les catégories ou les pages de listing, où le contenu éditorial est souvent limité.

---

### 👀 Aperçu SERP (Google Preview)

L'aperçu SERP vous montre **exactement** à quoi ressemblera votre contenu dans les résultats de recherche Google.

Vous disposez de deux modes d'aperçu :

- **Desktop** 🖥️ : l'affichage sur ordinateur
- **Mobile** 📱 : l'affichage sur smartphone

L'aperçu se met à jour **en temps réel** au fur et à mesure que vous remplissez les champs SEO.

Les éléments affichés sont :
- L'**URL** de la page
- Le **titre SEO** (en bleu)
- La **meta description** (en gris)

> 💡 **Astuce** : Si le titre apparaît tronqué (avec « ... ») dans l'aperçu, c'est qu'il est trop long. Raccourcissez-le pour qu'il s'affiche entièrement.

---

### 📘 Open Graph (Facebook / réseaux sociaux)

L'onglet **Open Graph** contrôle l'apparence de votre contenu lorsqu'il est partagé sur **Facebook, LinkedIn, WhatsApp** et la plupart des réseaux sociaux.

#### Champs disponibles

- **Titre OG** : le titre affiché lors du partage (peut différer du titre SEO)
- **Description OG** : la description lors du partage
- **Image OG** : l'image affichée dans la carte de partage (recommandé : 1200×630 px)
- **Type OG** : le type de contenu (article, page, etc.)

#### Aperçu Open Graph

Un aperçu visuel vous montre à quoi ressemblera votre contenu lorsqu'il sera partagé sur les réseaux sociaux. L'aperçu simule une **carte Facebook** avec l'image, le titre et la description.

> 💡 **Astuce** : Choisissez une image OG attrayante et au bon format (1200×630 pixels). C'est la première chose que les gens verront en découvrant votre contenu sur les réseaux sociaux !

---

### 🐦 Twitter Card

L'onglet **Twitter** fonctionne de la même manière qu'Open Graph, mais spécifiquement pour **X (Twitter)**.

#### Champs disponibles

- **Titre Twitter** : le titre affiché sur X
- **Description Twitter** : la description sur X
- **Image Twitter** : l'image de la carte (recommandé : 1200×628 px)
- **Type de carte** : Summary (petite image) ou Summary with Large Image (grande image)

#### Aperçu Twitter

Un aperçu simule l'affichage d'une **carte Twitter** avec votre contenu.

> 💡 **Astuce** : Si vous ne remplissez pas les champs Twitter, les valeurs Open Graph seront utilisées automatiquement comme solution de repli.

---

### 🧬 Schema Markup (Données structurées)

Le Schema Markup permet d'ajouter des **données structurées** à votre page. Ces données aident Google à mieux comprendre votre contenu et peuvent générer des **résultats enrichis** (étoiles, FAQ, breadcrumbs, etc.) dans les résultats de recherche.

#### Types de schema disponibles

Vous pouvez sélectionner un ou plusieurs types de schema :

- **Article** : pour les articles de blog
- **Organization** : pour les pages de présentation d'entreprise
- **Person** : pour les pages de profil
- **FAQ** : pour les pages de questions fréquentes
- **Product** : pour les pages produit
- **BreadcrumbList** : pour le fil d'Ariane
- Et d'autres selon votre configuration

> 💡 **Astuce** : Le schema « Article » est automatiquement recommandé pour les articles de blog. Pour les pages « À propos », utilisez « Organization ». Pour les FAQ, utilisez « FAQ ».

---

### 🤖 Directives Robots

Les directives robots contrôlent le comportement des moteurs de recherche vis-à-vis de votre page :

| Directive | Description |
|-----------|-------------|
| **Index** ✅ | Autorise Google à indexer cette page (elle apparaîtra dans les résultats) |
| **No Index** ❌ | Empêche Google d'indexer cette page |
| **Follow** ✅ | Autorise Google à suivre les liens présents sur cette page |
| **No Follow** ❌ | Empêche Google de suivre les liens |

> 💡 **Astuce** : Par défaut, laissez « Index » et « Follow » activés. Utilisez « No Index » uniquement pour les pages que vous ne voulez pas voir apparaître dans Google (ex : pages de remerciement, pages légales, conditions générales).

---

### ✅ Validation de publication

Votre CMS vérifie automatiquement la qualité SEO de vos contenus avant publication. Deux niveaux d'alerte existent :

#### ⛔ Blocages

Certaines vérifications **empêchent la publication** si elles ne sont pas satisfaites :
- Titre SEO manquant
- Meta description manquante

#### ⚠️ Avertissements

D'autres vérifications émettent un **avertissement** sans bloquer la publication :
- Titre SEO trop court ou trop long
- Meta description trop courte ou trop longue
- Mot-clé principal manquant
- Image OG manquante

> 💡 **Astuce** : Traitez les avertissements comme des recommandations fortes. Un contenu bien optimisé SEO aura beaucoup plus de chances d'apparaître dans les premiers résultats de Google.

---

### 🔗 URL canonique

L'URL canonique indique à Google quelle est la **version principale** d'une page, en cas de contenu similaire accessible via plusieurs URLs. En général, vous n'avez pas besoin de la modifier : elle est définie automatiquement.

> 💡 **Astuce** : Ne modifiez l'URL canonique que si votre développeur vous le demande explicitement. Une mauvaise URL canonique peut nuire à votre référencement.

---

### 🎯 Check-list SEO rapide

Avant de publier un contenu, vérifiez ces points :

- [ ] ✅ **Titre SEO** rempli (50-60 caractères)
- [ ] ✅ **Meta description** rédigée (120-160 caractères)
- [ ] ✅ **H1** défini et contenant le mot-clé
- [ ] ✅ **Mot-clé principal** renseigné
- [ ] ✅ **Image OG** sélectionnée (1200×630 px)
- [ ] ✅ **Texte alternatif** des images rempli
- [ ] ✅ **Aperçu SERP** vérifié (titre et description lisibles)
- [ ] ✅ **Aperçu social** vérifié (image et texte corrects)
