=== Astra Child ===

Tema hijo multi-marca para blogs (AXA, Quálitas, etc.).

== Configuración de marca ==

Ve a Apariencia → Personalizar → Marca del blog:

* Preset de marca (AXA, Quálitas o Personalizado)
* Colores individuales por token
* Texto de agente autorizado en el footer

== Widget de nota ==

En Apariencia → Widgets, usa el área **Widget superior de nota**
para colocar el formulario de cotización arriba de las categorías.

== Presets incluidos ==

* AXA — colores según guía Blog AXA 2026 (#00008F, #FF1721, etc.)
* Quálitas — colores del diseño original

== Desarrollo ==

Los CSS usan variables `--blog-*` definidas en inc/theme-config.php.
Para agregar una marca nueva, usa el filtro `astra_child_brand_presets`.
