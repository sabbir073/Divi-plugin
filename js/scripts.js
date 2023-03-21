$('.install_button').click(function(e) {
    e.preventDefault();
    modalLoading.init(true);
    var download_url = $(this).data('download');
    var theme_name = $(this).data('name');
    $.ajax({
        type: "POST",
        url: obj.ajaxurl,
        data:{action:'install_child_theme',download:download_url,name:theme_name},
        success:function() {
            setTimeout(function(){// wait for 1 sec
                location.reload(); // then reload the page.
           }, 1000);
            modalLoading.init(true);

        }

   });
});

$('.activate_theme').click(function(e) {
    e.preventDefault();
    modalLoading.init(true);
    var theme_directory_name = $(this).data('name');
    $.ajax({
        type: "POST",
        url: obj.ajaxurl,
        data:{action:'activate_child_theme',name:theme_directory_name},
        success:function(html) {
            setTimeout(function(){// wait for 1 sec
                location.reload(); // then reload the page.
            }, 1000);
            modalLoading.init(true);

        }

   });
});

$(document).ready(function() {
    $('.import-layouts').click(function(e) {
      e.preventDefault();
      modalLoading.init(true);
      var download_url = $(this).data('download-url');
      var item_title = $(this).data('item-title');
      var item_type = $(this).data('item-type');
      var $button = $(this); // select the button element
      $button.html("Importing..."); // change the text to "Importing..."
  
      $.ajax({
        type: "POST",
        url: obj.ajaxurl,
        data: {
          action: 'import_divi_layout',
          download_url: download_url,
          item_title: item_title,
          item_type: item_type,
        },
        success: function(html) {
          setTimeout(function() {
            // wait for 1 sec, then change the text back to the original
            $button.html('Imported!');
            $button.css('background-color', '#2271b1');
            $('#openModalLoading').hide();
          }, 1000);
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.log("AJAX request failed: " + textStatus + ", " + errorThrown);
        }
      });
    });
  });
