# eCommerce Reseller Website

# Installation
### Requirements
* PHP version >=8.0
* Shell access

## Local HTTPS development

The Docker stack exposes the application at `https://framework.local:9443`
through Caddy. Start it with:

```shell
docker compose up -d --build
```

Add `127.0.0.1 framework.local` to `/etc/hosts`. Caddy uses a local internal
certificate authority for this development hostname; trust its root
certificate from the `caddy_data` volume if your browser reports the
certificate as untrusted.

HTTP is also available at `http://framework.local:9080`.

Local development uses safe built-in reseller defaults and does not call the
remote hosting API. Set `USE_REMOTE_CONFIG=true` in the Docker environment if
you need to test against live reseller configuration.

The Compose stack enables `LOCAL_DOMAIN_AVAILABILITY`, which supplies
deterministic domain rows when no local WHOIS provider is configured. Set
`LOCAL_DOMAIN_TAKEN_TLDS=net,org` to mark selected TLDs as taken during local
UI testing. Keep this fixture disabled in deployed environments.

For a deployed reseller, put the Camoo.Hosting credentials in the untracked
`config/.env` file:

```dotenv
CAMOO_HOSTING_EMAIL="you@example.com"
CAMOO_HOSTING_PASSWORD="your-password"
ACCESS_TOKEN_SALT="long-random-secret"
USE_REMOTE_CONFIG=true
```

Do not place credentials in templates, controllers, or committed example
files. Existing installations using the legacy `cm_email` and `cm_passwd`
names remain supported.

Static assets are served with a one-week browser cache. CSS and JavaScript
references include a file-modification version, so changing an asset creates a
new URL automatically. Twig templates reload on every request in development;
production uses the compiled template cache. Clear compiled templates with
`./bin/camoo cleanup:tpl` after deployment if a deployment changes template
loading behavior.

### clone project
```shell
 cd /home/user
 sudo -u user git clone https://github.com/camoo/e-reseller.git public_html
 cd public_html
 sudo -u user composer install --no-dev
```

### Clear Cache
```shell
 cd /home/user/public_html/
 # clear all cache types
 sudo -u user ./bin/camoo cleanup:all
 # clear only template cache
 sudo -u user ./bin/camoo cleanup:tpl
 # clear only translation catalogs
 sudo -u user ./bin/camoo cleanup:translations
```

### Customisation
The base styles are loaded in this order:

1. Vendor and legacy stylesheets
2. `web/css/style.css` and `web/css/responsive.css`
3. `web/css/modern.css` (the default modern design layer)
4. `web/css/custom.css` (optional tenant/reseller override layer)

Because `custom.css` is loaded last, it is the supported place for branding
and per-tenant visual changes. Do not edit `style.css` or `modern.css` for a
single reseller; those files are shared base layers.

#### Override design tokens

The modern layer exposes CSS variables at `:root`. Add only the values you
want to change:

```css
/* web/css/custom.css */
:root {
    --camoo-brand: #0b5ed7;
    --camoo-brand-dark: #063b8d;
    --camoo-accent: #f59e0b;
    --camoo-radius: 12px;
}
```

Tokens are preferred because one change updates buttons, links, cards, and
interactive states consistently. For component-specific changes, use the
existing semantic selectors:

```css
.header-area { background: #101828; }
.main-menu ul li a { color: #fff; }
.single_prising, .single_features { border-radius: 12px; }
```

Keep reseller rules scoped to the component being changed and avoid broad
selectors such as `*` or `!important` unless there is a documented legacy
compatibility reason.

#### Override translations and language

Translations use CakePHP PO catalogs from `src/Locale`. The application
catalog is the override layer, so a reseller can change wording without
editing the SDK or controller code:

```text
src/Locale/<locale>/default.po
```

For example, to change the French page title for the cart, edit
`src/Locale/fr_CM/default.po`:

```po
msgid "Votre Panier"
msgstr "Mon panier"
```

The `msgid` must match the string passed to `__()` and `msgstr` is the text
shown to the user. Keep the PO file UTF-8 encoded. Add a new locale by copying
the catalog structure, for example `src/Locale/en_CM/default.po`, and select
it in `config/.env`:

```dotenv
APP_LOCALE=en_CM
```

For text written directly in a Twig template, use the `t()` helper so it is
also overridable from the PO catalog:

```twig
<h2>{{ t('Need help?') }}</h2>
```

Then add the matching entry to `src/Locale/<locale>/default.po`:

```po
msgid "Need help?"
msgstr "Besoin d'aide ?"
```

The default remains `fr_CM` when `APP_LOCALE` is not set. Translation catalogs
are cached by CakePHP in `tmp/cache/persistent/`; after changing a PO file,
clear the application cache:

When the Camoo.Hosting reseller configuration contains a valid `locale` value,
it takes precedence over `APP_LOCALE`. This allows the hosting dashboard to
offer a language selector. The frontend accepts the locale only when a matching
`src/Locale/<locale>/default.po` catalog is installed; otherwise it safely
continues with the configured/default locale.

The hosting configuration API should return the selected value as:

```json
{
  "locale": "en_CM"
}
```

The reseller dashboard can expose this as a select box populated from the
locales supported by the deployed frontend. The API client preserves this
field on `Camoo\Hosting\Entity\Configuration` for entity-based consumers.

```shell
./bin/camoo cleanup:all
```

In Docker, run the command inside the PHP container:

```shell
docker compose run --rm app ./bin/camoo cleanup:all
```

The translation layer and CSS layer are independent: use PO files for visible
wording and `custom.css` for colors, typography, spacing, and layout.

#### Customize showcase content site-wide

The public showcase copy is reseller data, not fixed template copy. The same
configuration tree drives the homepage, navigation, SEO metadata, shared
support/footer sections, page headings, authentication, domain ordering, and
the basket/payment page. The defaults live in `config/app.php` under
`HomeContent`. A reseller can override them without editing Twig in either of
two ways:

1. Preferred for a managed reseller: return a `home_content` object from the
   hosting configuration endpoint. The object may contain `seo`, `well_known`,
   `navigation`, `hero`, `support_block`, `questions`, `footer`, `pages`,
   `tld_badges`, `pricing`, and `features`; omitted values keep the defaults.
   The `well_known` object can override `robots`, `sitemap`, `llms`,
   `security_txt`, `change_password_redirect`, and newline-separated
   `sitemap_urls` without editing application code. To connect the footer
   newsletter to the Hosting dashboard, set
   The showcase newsletter uses the dedicated
   `/v1/showcase/subscribers` endpoint. It stores each address in the Hosting
   `subscribers` table with purpose `showcase_newsletter` and the authenticated
   reseller's `user_id`; it does not use mailing-list identifiers or
   `list_subscribers`.
2. For a local or self-hosted installation: set `HOME_CONTENT_JSON` in
   `config/.env`. This is merged after the API response, so `.env` is the
   final local override.

For longer content, copy `config/app.local.php.dist` to
`config/app.local.php` and edit the returned PHP array. This is easier to
maintain than JSON and is loaded automatically. The complete precedence is:
base `app.php`, PHP local overrides, hosting API `home_content`, then
`HOME_CONTENT_JSON`.

Example:

```dotenv
HOME_CONTENT_JSON='{"seo":{"title":"Mon hébergeur","description":"Noms de domaine et hébergement web pour votre activité."},"hero":{"eyebrow":"Votre présence en ligne commence ici","title":"Réservez votre domaine"},"pages":{"contact":{"title":"Parlons de votre projet"}},"pricing":{"title":"Choisissez votre offre"},"tld_badges":[{"name":".com","tag":"Populaire","class":"tld_pill_popular"}]}'
```

Each content value is escaped as normal template text. Default values are also
passed through `t()`, so they can be translated in
`src/Locale/<locale>/default.po`. Custom reseller wording is displayed as
provided and does not require a code deployment. Use the existing feature
flags to remove product areas that are not sold.

Domain badge prices are refreshed from the Camoo.Hosting SDK domain-prices
endpoint and cached for one hour. This keeps the homepage aligned with the
reseller's active domain packages; configured
`tld_badges` values still control the labels, tags, and ordering. Set
`DOMAIN_PRICES_FROM_SDK=false` only when a deployment intentionally wants to
hide SDK-driven domain pricing. The local deterministic availability fixture
may provide these values during development, but is disabled by default.

#### Enable only the products you sell

Product availability is controlled first from `config/.env`:

```dotenv
FEATURE_DOMAINS=true
FEATURE_EMAILS=true
FEATURE_SSL=true
FEATURE_HOSTING=false
FEATURE_SERVERS=false
```

For a domains, email, and SSL-only reseller, set `FEATURE_HOSTING=false` and
`FEATURE_SERVERS=false`. Disabled products disappear from navigation and
public sections, and their direct application routes are rejected as well.
The email, SSL, and server flags are already part of the shared configuration
contract so dedicated product screens can use the same controls when enabled.
Restart PHP workers after changing `.env` so the new configuration is loaded.

#### ADD Custom CSS
Upload a file with the exact name `custom.css` into
`/home/user/public_html/web/css/`. It is optional; when absent, no extra
stylesheet tag is rendered.

#### ADD Custom JS
Upload a file with the exact name `custom.js` into
`/home/user/public_html/web/js/`. It is optional and is loaded after the base
JavaScript files.

#### Cache behavior

Every local CSS and JavaScript URL receives a `?v=<file modification time>`
query string. After uploading or editing an asset, the URL changes
automatically and browsers fetch the new file. Static assets are otherwise
cached for one week. If an old style still appears, perform a hard refresh or
clear the reverse-proxy/browser cache; do not add a random timestamp to the
template.

#### Change logo and favicon
Resellers can replace the logo and favicon without editing templates or PHP:

1. Upload the files to `/home/user/public_html/web/img/` for the logo and
   `/home/user/public_html/web/` for the favicon.
2. Set the exact filenames in `config/.env`:

```shell
LOGO_FILE_NAME="my-site-logo.png"
FAVICON_FILE_NAME="my-site-favicon.ico"
```

The logo is used in the header, footer, login dialog, and registration dialog.
The favicon is used on every layout. If a file is missing or the filename is
invalid, the application falls back to `logo.png` or `favicon.ico`. Asset URLs
are automatically versioned, so browsers fetch replacements immediately.

# Troubleshooting
in case that you site is displaying only Error.
Please make sure that:
* You add the ip-address of your server to our authorized list. To do so you have to login to your dashboard then under the reseller menu find `API`
* Or check your `config/.env` and make sure that you credentials are correct.

# Examples
* Home
![Home page](https://github.com/camoo/e-reseller/raw/master/Screenshot%20from%202022-12-18%2012-53-48.png)

* Package example
![Packages](https://github.com/camoo/e-reseller/raw/master/Screenshot%20from%202022-12-18%2012-55-02.png)

* Purchase process
![Purchase Package](https://github.com/camoo/e-reseller/raw/master/Screenshot%20from%202022-12-18%2012-55-44.png)

* Payment methods
![Payment method](https://github.com/camoo/e-reseller/raw/master/Screenshot%20from%202022-12-18%2012-56-26.png)

_Powered by Camoo.Hosting_
