(function($) {
  $(document).ready(function() {
    var container = document.querySelectorAll('#edit-search-api-views-fulltext-wrapper')[0];
    var elements = container.querySelectorAll('select');
    Array.prototype.forEach.call(elements, function(el, i) {
      el.addEventListener("change", function() {
        this.form.submit();
      });
    });
  });
})(jQuery);
// (function($){
//   /**
//    * Hide submit button in select widget facet.
//    **/
//   Drupal.behaviors.ExampleHideSubmit = {
//   attach: function(context) {
//     $('.chm-search-select-facet .ctools-auto-submit-click:not(.chm_search-hide-submit-processed)', context)
//     .addClass('chm_search-hide-submit-processed').hide();
//   }}

// })(jQuery);

// (function ($, window, document) {

//   Drupal.Select2.functionsScope.chm_search_format_selection = function (term) {

//     if (term.hover_title) {
//       return term.hover_title;
//     }

//     return term.text;
//   };

// })(jQuery, window, document);
