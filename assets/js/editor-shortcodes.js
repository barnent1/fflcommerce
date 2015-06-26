(function() {
 if(typeof tinymce == 'undefined') return;
 tinymce.create('tinymce.plugins.fflcommerceShortcodes', {
    init : function(ed, url) {

      ed.addButton('fflcommerce_add_cart', {
        title: "Add To Cart",
        image: url+"/../images/icons/shortcodes/icon08.png",
        onclick: function() {
          ed.execCommand( 'mceInsertContent', false, '[add_to_cart id="1"]' );
        }
      });

      ed.addButton('fflcommerce_show_product', {
        title: "Show Product",
        image: url+"/../images/icons/shortcodes/icon06.png",
        onclick: function() {
          ed.execCommand( 'mceInsertContent', false, '[product id="99"]' );
        }
      });

      ed.addButton('fflcommerce_show_category', {
        title: "Show Category",
        image: url+"/../images/icons/shortcodes/icon07.png",
        onclick: function() {
          ed.execCommand( 'mceInsertContent', false, '[fflcommerce_category slug="category-name" per_page="8" columns="4" pagination="yes"]' );
        }
      });

      ed.addButton('fflcommerce_show_featured_products', {
        title: "Show Featured Products",
        image: url+"/../images/icons/shortcodes/icon05.png",
        onclick: function() {
          ed.execCommand( 'mceInsertContent', false, '[featured_products per_page="12" columns="4" pagination="yes"]' );
        }
      });

      ed.addButton('fflcommerce_show_selected_products', {
        title: "Show Selected Products",
        image: url+"/../images/icons/shortcodes/icon04.png",
        onclick: function() {
          ed.execCommand( 'mceInsertContent', false, '[products ids="1, 2, 3, 4, 5" pagination="yes"]' );
        }
      });

      ed.addButton('fflcommerce_product_search', {
        title: "Product Search Form",
        image: url+"/../images/icons/shortcodes/icon03.png",
        onclick: function() {
          ed.execCommand( 'mceInsertContent', false, '[product_search]' );
        }
      });

      ed.addButton('fflcommerce_recent_products', {
        title: "Recent Products",
        image: url+"/../images/icons/shortcodes/icon02.png",
        onclick: function() {
          ed.execCommand( 'mceInsertContent', false, '[recent_products per_page="12" columns="4" pagination="yes"]' );
        }
      });

      ed.addButton('fflcommerce_sale_products', {
        title: "Sale Products",
        image: url+"/../images/icons/shortcodes/icon06.png",
        onclick: function() {
          ed.execCommand( 'mceInsertContent', false, '[sale_products]' );
        }
      });


    }
 });
 tinymce.PluginManager.add('fflcommerceShortcodes', tinymce.plugins.fflcommerceShortcodes);
})();