# WARNING - THIS IS A BETA
**This is a beta and especially the plugin name will be changed from *dynblocks* to *dyncomp*.**

# Mercator DynBlocks (WinterCMS) 

DynBlocks enables rendering and instantiating CMS components from places where you normally cannot attach components
(e.g., Static Pages `.block` content rendered via Winter.Pages / Winter.Blocks).

It provides:

- Twig tag: `{% dyncomponent %}` (renders a component to HTML)
- Twig functions:
  - `dynComponentRender()` (renders a component to HTML)
  - `dynComponent()` (returns the component instance for custom rendering / data access)
  - `dynComponentPageVars()` (reads a page variable from the active CMS controller)

No Twig filters are registered by this plugin.

---

## Installation

Copy the plugin folder into your project:

```
plugins/mercator/dynblocks
```

Then update:

```bash
php artisan winter:up
php artisan cache:clear
```

---

## Usage

### 1) Render a component via Twig tag

Render the component directly inside a `.block`:

```twig
{% dyncomponent 'blogPosts' postsPerPage=10 postPage='blog/post' %}
```

The tag supports `key=value` properties (Twig expressions are allowed).

### 2) Render a component via Twig function

Equivalent to the tag, but using a function call:

```twig
{{ dynComponentRender('blogPosts', {
  postsPerPage: 10,
  postPage: 'blog/post'
}, 'blogPosts') }}
```

Arguments:

- `name` (string): component alias/name (e.g. `blogPosts`)
- `props` (array, optional): component properties
- `alias` (string, optional): alias of the component instance on the page (recommended to keep AJAX handlers stable)

Return value:

- string (HTML)

### 3) Get a component instance and render yourself

This is useful when you want to read data populated by the component (`onRun()`) and render your own markup.

```twig
{% set cmp = dynComponent('blogPosts', {
  postsPerPage: 10,
  postPage: 'blog/post'
}, 'blogPosts') %}

{% if cmp %}
  {# Example: if the component exposes a public `posts` property #}
  {% set posts = attribute(cmp, 'posts') %}
  <ul>
    {% for post in posts %}
      <li><a href="{{ post.url }}">{{ post.title }}</a></li>
    {% endfor %}
  </ul>
{% endif %}
```

Arguments are the same as `dynComponentRender()`.

Return value:

- the component object instance (or `null`)

### 4) Read page variables set by a component

Some components populate `$this->page['posts']` (page variables) rather than a public component property.
Use `dynComponentPageVars()` to fetch controller vars:

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

Return value:

- mixed

---

## AJAX handlers and component alias

If your component partials use `data-request="{{ __SELF__ }}::onSomething"`, the component alias is resolved automatically.

If your markup hardcodes `data-request="SomeAlias::onSomething"`, ensure you mount the component with that alias:

```twig
{{ dynComponentRender('blogPosts', { postsPerPage: 10 }, 'SomeAlias') }}
```

---

## Configuration

File:

```
plugins/mercator/dynblocks/config/dynblocks.php
```

Key options:

- `allowlist.enabled`
  - `false` = allow all components
  - `true` = allow only `allowed_names` OR `allowed_class_prefixes`
- `static_pages_only`
  - `true` = only run when the current page contains the `staticPage` component
  - `false` = allow in any CMS page / partial / block
- `fail_loud`
  - `true` = render an HTML error box for missing/blocked components (debugging)
  - `false` = fail silently (empty string)

---

## License

MIT. See `LICENSE`.
