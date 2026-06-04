# Mercator DynBlocks (WinterCMS)

DynBlocks enables rendering and instantiating CMS components from places where you normally cannot attach components — e.g. Static Pages `.block` content rendered via Winter.Pages / Winter.Blocks.

It provides:

- Twig tag: `{% dynComponent %}` (renders a component to HTML)
- Twig functions:
  - `dynComponentRender()` — renders a component to HTML
  - `dynComponent()` — returns the component instance for custom rendering / data access
  - `dynComponentPageVars()` — reads a page variable from the active CMS controller

---

## Installation

Copy the plugin folder into your project:

```
plugins/mercator/dynblocks
```

Then run:

```bash
php artisan winter:up
php artisan cache:clear
```

---

## Usage

### 1) Render a component via Twig tag

```twig
{% dynComponent 'blogPosts' postsPerPage=10 postPage='blog/post' %}
```

The tag supports `key=value` properties (Twig expressions are allowed).

### 2) Render a component via Twig function

```twig
{{ dynComponentRender('blogPosts', {
  postsPerPage: 10,
  postPage: 'blog/post'
}, 'blogPosts') }}
```

Arguments:

- `name` (string): component alias/name
- `props` (array, optional): component properties
- `alias` (string, optional): alias for the component instance — set this to keep AJAX handlers stable

Return value: string (HTML)

### 3) Get a component instance and render yourself

Useful when you want to read data populated by the component in `onRun()` and write your own markup.

```twig
{% set cmp = dynComponent('blogPosts', {
  postsPerPage: 10,
  postPage: 'blog/post'
}, 'blogPosts') %}

{% if cmp %}
  {% set posts = attribute(cmp, 'posts') %}
  <ul>
    {% for post in posts %}
      <li><a href="{{ post.url }}">{{ post.title }}</a></li>
    {% endfor %}
  </ul>
{% endif %}
```

Arguments are the same as `dynComponentRender()`. Return value: component object (or `null`).

### 4) Read page variables set by a component

Some components write to `$this->page['key']` rather than a public property. Use `dynComponentPageVars()` to read them:

```twig
{% set _ = dynComponent('blogPosts', { postsPerPage: 10 }, 'blogPosts') %}
{% set posts = dynComponentPageVars('posts', []) %}

<ul>
  {% for post in posts %}
    <li>{{ post.title }}</li>
  {% endfor %}
</ul>
```

Arguments:

- `key` (string): variable name
- `default` (mixed, optional): returned if the variable is not set

Return value: mixed

---

## AJAX handlers and component alias

If your component partials use `data-request="{{ __SELF__ }}::onSomething"`, the component alias is resolved automatically.

If your markup hardcodes `data-request="SomeAlias::onSomething"`, mount the component with that alias explicitly:

```twig
{{ dynComponentRender('blogPosts', { postsPerPage: 10 }, 'SomeAlias') }}
```

---

## Configuration

File: `plugins/mercator/dynblocks/config/dynblocks.php`

| Option | Values | Description |
|---|---|---|
| `allowlist.enabled` | `false` / `true` | `false` = allow all components; `true` = restrict to `allowed_names` / `allowed_class_prefixes` |
| `static_pages_only` | `true` / `false` | `true` = only run on pages that include the `staticPage` component |
| `fail_loud` | `true` / `false` | `true` = render an HTML error box for missing/blocked components; `false` = fail silently |

---

## License

MIT. See `LICENSE`.
