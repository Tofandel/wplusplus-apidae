# W++ Apidae
A Wordpress Plugin allowing you to create Twig templates for [Apidae](https://www.apidae-tourisme.com/)

This plugin has some pro features, you can purchase a licence [here](https://tukangroup.com/webshop/premium-plugins/wplusplus-apidae/)

## Shortcodes
Required parameters are marked with a `*`

### Apidae_List :
Displays a list of Apidae objects from a defined list template

**Parameters:**
 * `'template' *        - string -`     The slug of the list template
 * `'detail_id' *       - int    -`     The ID of the detail page
 * `'selection_ids' *   - int    -`     Comma separated list of apidae selection's id
 * `'paged'             - bool   -`     Whether the list should be paginated or not (defaults to 'true')
 * `'nb_result'         - int    -`     The number of result per page (defaults to '30')
 * `'order'             - string -`     How do you want the result to be ordered (available: 'NOM','IDENTIFIANT','RANDOM','DATE_OUVERTURE','PERTINENCE','DISTANCE') (defaults to 'PERTINENCE')
 * `'reverse_order'     - bool   -`     Whether you want the order to be ascendant or descendant (defaults to 'false' => ascendant)
 * `'langs'             - string -`     Comma separated list of languages that you want to receive in the template (defaults to 'fr')
 * `'search_fields'     - string -`     Where do you want the search query to look in (available: 'NOM', 'NOM_DESCRIPTION', 'NOM_DESCRIPTION_CRITERES') (defaults to 'NOM_DESCRIPTION_CRITERES')
 * `'detail_scheme'     - string -`     The link scheme to the detail template (defaults to '/%type%/%nom.libelle%/%localisation.adresse.commune.nom%') you can use any path from the apidae object

### Apidae_Detail
Displays a single Apidae object from a defined single object template

**Parameters:**
 * `'template' *     - string -`     The slug of the detail template
 * `'langs'          - string -`     Comma separated list of languages that you want to receive in the template (defaults to 'fr')

### Apidae_Categories
Displays a list of categories with links for the current Apidae_List

**Parameters:**
 * `'categories' *       - string -`    Comma separated list of the Apidae categories slug you want displayed (you have to create them first in the apidae options)
 * `'all_link'           - bool -`      Whether to display the 'All' link or not (defaults to true)

### Apidae_Map :
Displays a google map with the markers from the Apidae_List or Apidae_Detail present on the page

**Parameters:**
 * `'width'                -  string -` The width of the map element (defaults to '100%')
 * `'height'               -  string -` The height of the map element (defaults to '300px')
 * `'zoom'                 -  int    -` The zoom level between 1 and 21, leave blank for auto
 * `'type'                 -  string -` The type of the map (available: 'roadmap','satellite','hybrid','terrain') (defaults to 'roadmap')
 * `'marker_animation'     -  string -` The marker animation on map load (available: 'none','bounce','drop') (defaults to 'drop')
 * `'animation_duration'   -  int    -` The time it will take for all the animations to be completed (defaults to '2000' => 2 seconds)
 * `'disable_ui'           -  bool   -` If you want to disable the controls for the map (defaults to 'false')
 * `'disable_scrollwheel'  -  bool   -` If you want to disable zooming with the scrollwhell (defaults to 'false')
 * `'draggable'            -  bool   -` If the user should be able to move the map (defaults to 'true')
 * `'use_clusters'         -  bool   -` If you want close markers to be grouped on the map (defaults to 'false')
 * `'preset'               -  string -` The color preset you want to use (available:
        'apple-maps-esque','avocado-world','becomeadinosaur','black-white','blue-essence','blue-water','cool-grey','flat-map','greyscale','light-dream','light-monochrome',
        'mapbox','midnight-commander','neutral-blue','pale-down','paper','retro','shades-of-grey','subtle-grayscale','ultra-light-with-labels','unsaturated-browns')

### Apidae_Search :
Displays a search form for a list

**Parameters:**
 * `'url'                    -  string -` Where to send the search to (Should be an url to a Page with an Apidae_List shortcode)
 * `'date_inputs'            -  bool   -` Whether to display the dates input (defaults to 'true')
 * `'categories_input'       -  bool   -` Whether to display the dates input (defaults to 'true')
 * `'search_input'           -  bool   -` Whether to display the keyword search input (defaults to 'true')
 * `'start_placeholder'      -  string -` The placeholder for the start date input
 * `'end_placeholder'        -  string -` The placeholder for the end date input
 * `'search_placeholder'     -  string -` The placeholder for the search input
 * `'categories_placeholder' -  string -` The placeholder for the categories input
 * `'submit_title'           -  string -` The tooltip for the submit button
 * `'submit_text'            -  string -` The text of the submit button
 
### Apidae_Widget :
Displays an Apidae widget

**Parameters:**
 * `'widget_id' *     -  int    -` The ID of the [widget](https://base.apidae-tourisme.com/diffuser/widget/)
 * `'width'           -  string -` The width of the widget (defaults to '100%')
 * `'height'          -  string -` The height of the widget (defaults to '700px')
 
## Twig templates

You should take a look a the [twig documentation](https://twig.symfony.com/doc/2.x/templates.html) if you are not familiar with twig

You can use wordpress shortcodes in the templates.

If you need to add functions or filters to the twig templates you can hook to the 'apidae_twig_functions' and the 'apidae_twig_filters' filters

```php
function my_function_callable() {
    return "Foo";
}
add_filter('apidae_twig_functions', function($functions) {
    $functions['my_function'] = new Twig_Function( 'my_function', 'my_function_callable' );
    return $functions;
});
```


**Available Functions:**
- `enqueue_script`: [wp_enqueue_script](https://developer.wordpress.org/reference/functions/wp_enqueue_script/) function of wordpress
- `enqueue_style`: [wp_enqueue_style](https://developer.wordpress.org/reference/functions/wp_enqueue_style/) function of wordpress
- `getCategoryFromObject(o)`: Get the approximated category of an apidae object based on the Apidae Categories defined on the Backend

**Available Filters:**
- `slugify`: This filter slugify strings passed to it (ex: `"I love Tukan"` => `"i-love-tukan"`)
- `applyScheme(o)`: This filter takes an object as a parameter and apply the scheme given in the string
```twig
{% set test = "%nom.libelle%, %id%"|applyScheme({nom:{libelle:"Tukan"},id:1950}) %}
{{ dump(test) }}
"Tukan, 1950"
```
- `orderBy(path)`: This filter takes a string as a parameter and will order an array based on the value that the given path will have for each iteration
```twig
{% set o = [ { params: { id: 1, order: 2 } }, { params: { id: 2, order: 1 } } ] %}
{{ dump(o|orderBy("params.order")) }}
```
```php
array (
  0 => 
  array (
    'params' => 
    array (
      'id' => 2,
      'order' => 1,
    ),
  ),
  1 => 
  array (
    'params' => 
    array (
      'id' => 1,
      'order' => 2,
    ),
  ),
)
```
- `groupBy(path)`: This filter takes a string as a parameter and will group an array based on the value that the given path will have for each iteration
```twig
{% set o = [ { params: { id: 1, group: 2 } }, { params: { id: 2, group: 1 } }, { params: { id: 3, group: 1 } } ] %}
{{ dump(o|orderBy("params.order")) }}
```
```php
array (
  2 => 
  array (
    'params' => 
    array (
      'id' => 1,
      'group' => 2,
    ),
  ),
  1 => 
  array (
    0 => 
    array (
      'params' => 
      array (
        'id' => 2,
        'group' => 1,
      ),
    ),
    1 => 
    array (
      'params' => 
      array (
        'id' => 3,
        'group' => 1,
      ),
    ),
  ),
)
```
### Shared blocks

##### scripts 
In this block you should enqueue your scripts with enqueue_script/enqueue_style or define them in script/style tags (First option is preferred since else you won't get browser caching)

##### layout
This is the root block and all the content have to be displayed inside

##### marker
This block contains the script tag for the map marker

### List Template Blocks
If you need to go further check out the [twig base file](https://github.com/Tofandel/wplusplus-apidae/blob/v1/templates/list-base.twig)

##### search_form
The block that displays the Apidae_Search shortcode
```twig
{% block search_form %}
    [Apidae_Search]
{% endblock search_form %}
```

##### pagination
The block that displays the header and footer pagination
```twig
{% block pagination %}
    {% if totalPages > 1 %}
        {{ paginate(urlScheme, totalPages, currentPage, 7, '<<', '>>') }}
    {% endif %}
{% endblock pagination %}
```
##### loop
This is where we will loop trough each apidae object to display them
```twig
{% block loop %}
    {% for o in searchResult %}
        {% set link = siteUrl ~ '/' ~ detailPageSlug ~ (detailScheme|applyScheme(o)|lower|slugify(false)) ~ '/id/' ~ o.id %}
        {# ... do stuff here #}
    {% endfor %}
{% endblock loop %}
```

##### single
This block is used to display what's inside a apidae object that will be defined in the 'o' variable, the 'link' variable will contain the link to the detail page of that object

##### no_result
This is the block displayed when no result is found


### Detail Template Blocks
If you need to go further check out the [twig base file](https://github.com/Tofandel/wplusplus-apidae/blob/v1/templates/detail-base.twig)

##### return
This block displays the return link
```twig
{% block return %}
    {% if referer is not empty %}
        <a class="retlink" href="{{ referer }}">{{ __('< Return') }}</a>
    {% endif %}
{% endblock return %}
```

##### title
This block displays the title

##### category
This block displays the category

##### images
This block displays the slider of images or a single image

##### tabs
This block displays tabs defined in blocks to add a new tab you need to define the tabs variable
```twig
{% block my_custom_tab %}
<h3>My custom tab</h3>
{% endblock %}
{% set tabs = {
	description: "Description",
	localisation: "Localisation",
	tarifs_reservation: "Tarifs et Reservation",
	moyencommunication: "Informations de Contact",
	prestations: "Prestations",
	my_custom_tab: "My custom tab"
} %}
```
