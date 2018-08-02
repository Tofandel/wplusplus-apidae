# W++ Apidae
A Wordpress Plugin allowing you to create Twig templates for [Apidae](https://www.apidae-tourisme.com/)

## Apidae doc
Read the [apidae documentation](http://dev.apidae-tourisme.com/fr/documentation-technique)

## Twig Templates
Read the [twig documentation](https://twig.symfony.com/doc/2.x/templates.html)

## Shortcodes
Required parameters are marked with a `*`

### Apidae_List :
Displays a list of Apidae objects from a defined list template

**Parameters:**
 * `'template' *      - string -`     The slug of the list template
 * `'detail_id' *     - int    -`     The ID of the detail page
 * `'selection_ids' * - int    -`     Comma separated list of apidae selection's id
 * `'paged'         - bool   -`     Whether the list should be paginated or not (defaults to 'true')
 * `'nb_result'     - int    -`     The number of result per page (defaults to '30')
 * `'order'         - string -`     How do you want the result to be ordered (available: 'NOM','IDENTIFIANT','RANDOM','DATE_OUVERTURE','PERTINENCE','DISTANCE') (defaults to 'PERTINENCE')
 * `'reverse_order' - bool   -`     Whether you want the order to be ascendant or descendant (defaults to 'false' => ascendant)
 * `'langs'         - string -`     Comma separated list of languages that you want to receive in the template (defaults to 'fr')
 * `'search_fields' - string -`     Where do you want the search query to look in (available: 'NOM', 'NOM_DESCRIPTION', 'NOM_DESCRIPTION_CRITERES') (defaults to 'NOM_DESCRIPTION_CRITERES')
 * `'detail_scheme' - string -`     The link scheme to the detail template (defaults to '/%type%/%nom.libelle%/%localisation.adresse.commune.nom%') you can use any path from the apidae object

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
        