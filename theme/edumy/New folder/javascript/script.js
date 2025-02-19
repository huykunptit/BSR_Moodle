document.addEventListener("DOMContentLoaded", function() {
  
  var currentUrl = window.location.href;

  // URL match conditions
  if (currentUrl.includes("question/edit1.php") ||
      currentUrl.includes("question/bank/managecategories/category1.php?courseid=") ||
      currentUrl.includes("question/bank/managecategories/category.php?courseid=") ||
      currentUrl.includes("question/bank/importquestions/import1.php?courseid=") ||
      currentUrl.includes("question/bank/exportquestions/export1.php")) {

      // Hide breadcrumb widgets
      var breadcrumbWidgets = document.querySelector(".breadcrumb_widgets.ccn-clip-l");
      if (breadcrumbWidgets) {
          breadcrumbWidgets.style.display = "none";
      }

      // Remove &category or &lastchanged from the URL
      let regex = /(&category=[^&]*|&lastchanged=[^&]*)$/;

      if (regex.test(currentUrl)) {
          let newUrl = currentUrl.replace(regex, '').replace(/[?&]$/, ''); // Clean up trailing '?' or '&'

          // Update URL without reloading
          if (newUrl !== currentUrl) {
              window.history.replaceState(null, '', newUrl);
          }
      }

      // Hide secondary navigation menu
    //   var secondaryMoreMenu = document.querySelector(".ccn-4-navigation");
      if (secondaryMoreMenu) {
          secondaryMoreMenu.style.display = "none";
      }
  }

  // Add class to selected items in the tree structure
  document.querySelectorAll('div[role="treeitem"]').forEach(function(item) {
      if (item.getAttribute("aria-selected") === "true") {
          item.classList.add("text-nowrap");
      }
  });
});
document.addEventListener("DOMContentLoaded", function() {
  // Wait for the modal to open by using event delegation to listen for click events
  document.addEventListener('click', function(event) {
      // Check if the "LÆ°u" button was clicked
      if (event.target.matches('button[title="LÆ°u"]')) {
          // Find the textarea in the modal
          const textarea = document.querySelector('.tox-dialog textarea.tox-textarea');
          
          if (textarea) {
              console.log('Textarea found');
              // Log the current value of the textarea
              console.log('Textarea value:', textarea.value);
          } else {
              console.log("Textarea not found");
          }
      }
  });
});
document.addEventListener("DOMContentLoaded", function () {
  function waitForCodeMirror() {
    const codeMirrorEditor = document.querySelector('.CodeMirror');
    
    if (codeMirrorEditor && codeMirrorEditor.CodeMirror) {
      console.log('CodeMirror editor found');

      const initialContent = codeMirrorEditor.CodeMirror.getValue();

      function detectXSS(content) {
        const xssPattern = /<script|onerror|onload|javascript:|<img.*src=|<iframe|<object|<embed/i;
        return xssPattern.test(content);
      }

      codeMirrorEditor.CodeMirror.on('change', function () {
        let currentContent = codeMirrorEditor.CodeMirror.getValue();

        if (detectXSS(currentContent)) {
          alert("Cáº£nh bÃ¡o: Ná»™i dung báº¡n nháº­p vÃ o chá»©a mÃ£ Ä‘á»™c. Trang sáº½ Ä‘Æ°á»£c táº£i láº¡i vá»›i ná»™i dung an toÃ n trÆ°á»›c Ä‘Ã³.");
          window.location.reload();
        }
      });
    } else {
      setTimeout(waitForCodeMirror, 500);
    }
  }

  waitForCodeMirror();
});


// jQuery section wrapped in (function)
$(document).ready(function() {
  var quickmailBlock = $('.block_quickmail.block.list_block.block_with_controls.blog_tag_widget.yui3-dd-drop');
  if (quickmailBlock.length) {
      var currentUrl = window.location.href;
      console.log(currentUrl); // Check the current URL in the console
      console.log('1');
      if (!currentUrl.includes('course/view.php?id=')) {
          quickmailBlock.addClass('d-none');
      }
  }
});

// Run immediately without waiting for DOM content
(function() {
  // Get the current domain and path
  const currentDomain = window.location.origin;
  const currentPath = window.location.pathname;

  // Check if the current path is '/blocks/iomad_company_admin/index.php'
  if (currentPath === '/blocks/iomad_company_admin/index.php') {
      // Redirect immediately to '/blocks/iomad_company_admin/uploaduser.php'
      window.location.replace(`${currentDomain}/blocks/iomad_company_admin/uploaduser.php`);
  }
})();

(function($) {
  "use strict";

  // Preloader function
  function preloaderLoad() {
      if ($('.preloader').length) {
          $('.preloader').delay(200).fadeOut(300);
      }
      $(".preloader_disabler").on('click', function() {
          $("#preloader").hide();
      });
  }

  // Navbar scroll fix
  function navbarScrollfixed() {
      $('.navbar-scrolltofixed:not(.ccn_disable_stricky)').scrollToFixed();
  }

  // Execute some functions after window load
  $(window).on('load', function() {
      $(".ace-responsive-menu").aceResponsiveMenu({
          resizeWidth: '768', // Set the same in Media query
          animationSpeed: 'fast', //slow, medium, fast
          accoridonExpAll: false //Expands all the accordion menu on click
      });
  });

  // Other necessary event handlers
  $(window).on('scroll', function() {
      if ($('.scroll-to-top').length) {
          var strickyScrollPos = 100;
          if ($(window).scrollTop() > strickyScrollPos) {
              $('.scroll-to-top').fadeIn(500);
          } else if ($(this).scrollTop() <= strickyScrollPos) {
              $('.scroll-to-top').fadeOut(500);
          }
      }
  });

  $(document).on('ready', function() {
      navbarScrollfixed();
      preloaderLoad();
  });
  
})(jQuery);