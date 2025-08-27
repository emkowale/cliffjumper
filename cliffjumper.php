<?php
/**
 * Plugin Name: Cliffjumper
 * Plugin URI: https://github.com/emkowale/cliffjumper
 * Description: Color swatch dropdown for WooCommerce attributes. Custom list with arrow, no default swatch, fully synced to the real <select>.
 * Version: 1.0.5
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Eric Kowalewski
 * Author URI: https://erickowalewski.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cliffjumper
 * Domain Path: /languages
 * Update URI: https://github.com/emkowale/cliffjumper
 */

if (!defined('ABSPATH')) exit;

/* --- Enqueue external stylesheet --- */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'cliffjumper-style',
        plugin_dir_url(__FILE__) . 'style.css',
        [],
        '1.0.5'
    );
});

/* --- Script --- */
add_action('wp_footer', function () {
    if (!is_product()) return;
?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const HEX = {
    "Aquatic Blue": "#6cb9d4",
    "Awareness Pink": "#d56e99",
    "Athletic Heather": "#a6aaad",
    "Black Heather": "#373b3c",
    "Dark Green": "#243b33",
    "Coyote Brown": "#807559",
    "Charcoal": "#595856",
    "Gold": "#fc9e00",
    "Graphite Heather": "#676968",
    "Ice Blue": "#adcfe8",
    "Jet Black": "#1b1d1c",
    "Kelly": "#019a6b",
    "Light Sand": "#c7b7a8",
    "Natural": "#f7ecd6",
    "Olive Drab Green Heather": "#5e6249",
    "Red": "#ab1a27",
    "S. Green": "#c9fd31",
    "S. Orange": "#fd6602",
    "Team Purple": "#3b3269",
    "True Navy": "#212c3e",
    "True Royal": "#1c4887",
    "Vivid Teal Heather": "#4b8996",
    "White": "#f5f9fc",
    "Flush Pink": "#a62452"
  };

  const LIGHT_THRESHOLD = 200;
  function isLight(hex){
    if (!hex) return false;
    const c = hex.replace('#','');
    if (c.length !== 6) return false;
    const r = parseInt(c.slice(0,2),16),
          g = parseInt(c.slice(2,4),16),
          b = parseInt(c.slice(4,6),16);
    const L = 0.2126*r + 0.7152*g + 0.0722*b;
    return L > LIGHT_THRESHOLD;
  }

  const colorSelects = document.querySelectorAll('select[name*="color"], select[name*="pa_color"]');
  colorSelects.forEach(function(select){
    if (select.dataset.cjInit) return;
    select.dataset.cjInit = '1';

    const wrap = document.createElement('div');
    wrap.className = 'cj-wrap';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'cj-button';
    button.setAttribute('aria-haspopup','listbox');
    button.setAttribute('aria-expanded','false');

    const chip = document.createElement('span');
    chip.className = 'cj-chip cj-empty';

    const label = document.createElement('span');
    label.className = 'cj-label';
    label.textContent = select.options[select.selectedIndex]?.text?.trim() || 'Choose an option';

    const caret = document.createElement('span');
    caret.className = 'cj-caret';

    button.append(chip, label, caret);

    const list = document.createElement('div');
    list.className = 'cj-list';
    list.setAttribute('role','listbox');

    Array.from(select.options).forEach(function(opt){
      if (!opt.value) return;

      const row = document.createElement('div');
      row.className = 'cj-option';
      row.setAttribute('role','option');
      row.dataset.value = opt.value;

      const text = document.createElement('span');
      text.textContent = opt.text.trim();

      const sw = document.createElement('span');
      sw.className = 'cj-swatch';

      const hex = HEX[opt.text.trim()];
      if (hex){
        sw.style.background = hex;
        if (isLight(hex)) sw.classList.add('cj-light');
      }else{
        sw.style.background = '#ccc';
      }

      row.append(text, sw);
      list.appendChild(row);

      row.addEventListener('click', () => {
        select.value = opt.value;
        select.dispatchEvent(new Event('change', { bubbles:true }));

        label.textContent = opt.text.trim();
        if (hex){
          chip.style.background = hex;
          chip.classList.toggle('cj-light', isLight(hex));
          chip.classList.remove('cj-empty');
        }else{
          chip.style.background = '#ccc';
          chip.classList.remove('cj-empty');
        }

        list.querySelectorAll('.cj-option[aria-selected="true"]').forEach(n => n.setAttribute('aria-selected','false'));
        row.setAttribute('aria-selected','true');

        wrap.classList.remove('cj-open');
        button.setAttribute('aria-expanded','false');
      });
    });

    const currentText = select.options[select.selectedIndex]?.text?.trim();
    if (select.value){
      label.textContent = currentText;
      const h = HEX[currentText];
      if (h){
        chip.style.background = h;
        chip.classList.toggle('cj-light', isLight(h));
        chip.classList.remove('cj-empty');
      }
    }

    button.addEventListener('click', () => {
      const open = wrap.classList.toggle('cj-open');
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', (e) => {
      if (!wrap.contains(e.target)) {
        wrap.classList.remove('cj-open');
        button.setAttribute('aria-expanded','false');
      }
    });

    select.classList.add('cj-hidden');
    select.parentNode.insertBefore(wrap, select);
    wrap.append(button, list);

    const obs = new MutationObserver(() => {
      if (!select.value) {
        label.textContent = select.options[0]?.text?.trim() || 'Choose an option';
        chip.style.background = 'transparent';
        chip.classList.add('cj-empty');
      }
    });
    obs.observe(select, { childList: true, subtree: true });
  });
});
</script>
<?php
});


// === Auto-updates via GitHub (Plugin Update Checker) ===
add_action('plugins_loaded', function () {
    $pucPath = __DIR__ . '/includes/vendor/plugin-update-checker/plugin-update-checker.php';
    if (!file_exists($pucPath)) return;
    require $pucPath;

    $updateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/emkowale/cliffjumper', // public repo URL (no .git)
        __FILE__,                                   // main plugin file
        'cliffjumper'                               // plugin slug (folder name)
    );

    // Track main branch explicitly
    $updateChecker->setBranch('main');

    // Prefer GitHub Release assets (zip with top-level folder "cliffjumper/")
    if ($api = $updateChecker->getVcsApi()) {
        $api->enableReleaseAssets();
    }
});



