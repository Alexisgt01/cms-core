---
title: "Redirections"
icon: "heroicon-o-arrow-uturn-right"
order: 7
---

## ↪️ Redirections

Les redirections permettent de **rediriger automatiquement** les visiteurs d'une ancienne URL vers une nouvelle. C'est essentiel pour éviter les erreurs 404 (« Page non trouvée ») lorsque vous modifiez l'URL d'une page ou supprimez un contenu.

Accédez aux redirections via le menu **SEO → Redirections**.

---

### 🔀 Types de redirections

Votre CMS supporte plusieurs types de redirections, chacun ayant un usage spécifique :

| Code | Nom | Quand l'utiliser |
|------|-----|-----------------|
| **301** | Redirection permanente | L'ancienne URL a été **définitivement** remplacée. C'est le type le plus courant. Google transfère le référencement vers la nouvelle URL. |
| **302** | Redirection temporaire | L'ancienne URL sera **rétablie plus tard**. Par exemple, pendant une maintenance ou une promotion temporaire. |
| **307** | Redirection temporaire stricte | Comme la 302, mais préserve la méthode HTTP (usage avancé, rare). |
| **410** | Contenu supprimé | L'URL a été **supprimée volontairement** et ne sera pas remplacée. Indique à Google de retirer cette page de son index. |

> 💡 **Astuce** : Dans 90 % des cas, utilisez une **redirection 301**. C'est la meilleure option pour préserver votre référencement lorsque vous changez une URL.

---

### ➕ Créer une redirection

1. Cliquez sur **Créer** en haut à droite
2. Remplissez les champs :
   - **Chemin source** : l'ancienne URL à rediriger (ex : `/ancienne-page`)
   - **URL de destination** : la nouvelle URL vers laquelle rediriger (ex : `/nouvelle-page` ou `https://autresite.com/page`)
   - **Type** : choisissez le code de redirection (301, 302, 307, 410)
   - **Activée** : activez ou désactivez la redirection
3. Cliquez sur **Créer**

> 💡 **Astuce** : Pour le chemin source, indiquez uniquement le **chemin relatif** (ce qui vient après votre nom de domaine). Par exemple : `/blog/ancien-article` et non `https://monsite.com/blog/ancien-article`.

> ⚠️ **Important** : Pour une redirection de type **410** (contenu supprimé), le champ URL de destination n'est pas nécessaire puisqu'il n'y a pas de nouvelle page.

---

### ✏️ Activer / Désactiver une redirection

Vous pouvez **désactiver temporairement** une redirection sans la supprimer :

1. Éditez la redirection
2. Décochez la case **Activée**
3. Enregistrez

La redirection reste dans votre liste mais ne sera plus appliquée. Vous pourrez la réactiver à tout moment.

---

### 📊 Suivi des redirections

Chaque redirection dispose de **statistiques de suivi** :

- **Nombre de hits** : combien de fois cette redirection a été utilisée
- **Dernier hit** : la date et l'heure de la dernière utilisation

Ces informations sont visibles directement dans la liste des redirections et dans la page de détail.

> 💡 **Astuce** : Surveillez le nombre de hits pour savoir si vos anciennes URLs sont encore visitées. Si une redirection n'a plus de hits depuis longtemps, elle peut être conservée par sécurité (ça ne coûte rien) ou supprimée si vous préférez garder la liste propre.

---

### 🗺️ Plan de site (Sitemap)

Le **sitemap** est un fichier XML qui liste toutes les pages de votre site pour aider Google à les découvrir et les indexer. Sa configuration se trouve dans les **Réglages du blog**.

#### Configurer le sitemap

1. Allez dans **Blog → Réglages du blog**
2. Ouvrez l'onglet **Sitemap**
3. Configurez les options :
   - **Activer le sitemap** : active ou désactive la génération du sitemap
   - **Inclure les articles** : inclut les articles de blog dans le sitemap
   - **Inclure les pages** : inclut les pages dans le sitemap
   - **Inclure les catégories** : inclut les catégories dans le sitemap
   - **Fréquence de mise à jour** : indique à Google à quelle fréquence votre contenu change

> 💡 **Astuce** : Activez le sitemap et incluez-y tous vos contenus publics. C'est l'un des premiers éléments à configurer pour un bon référencement.

---

### 🎯 Bonnes pratiques pour les redirections

- **Créez toujours une redirection 301** lorsque vous changez le slug (URL) d'un article ou d'une page
- **Ne créez pas de chaînes de redirections** (A → B → C). Redirigez toujours directement vers la destination finale (A → C)
- **Vérifiez vos redirections** après les avoir créées en visitant l'ancienne URL dans votre navigateur
- **Utilisez le 410** pour les contenus définitivement supprimés, plutôt que de laisser une erreur 404
- **Gardez votre liste propre** : supprimez les redirections devenues inutiles (très anciennes, sans hits)
