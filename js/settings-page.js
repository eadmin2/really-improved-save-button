// Modern UI for Really Improved Save Button settings page

// Dropdown for default action
var dropdown = document.getElementById('risb-default-action-dropdown');
if (dropdown) {
  var toggle = document.getElementById('risb-dropdown-toggle');
  var menu = document.getElementById('risb-dropdown-menu');
  var input = document.getElementById('risb-default-action-input');
  var label = document.getElementById('risb-dropdown-selected-label');
  
  toggle.addEventListener('click', function(e) {
    e.preventDefault();
    var isOpen = menu.classList.contains('risb-animate-in');
    
    if (isOpen) {
      // Close dropdown
      menu.classList.remove('risb-animate-in');
      setTimeout(function() {
        menu.style.display = 'none';
      }, 280); // Match CSS transition duration
    } else {
      // Open dropdown
      menu.style.display = 'block';
      // Force reflow for animation
      void menu.offsetWidth;
      menu.classList.add('risb-animate-in');
    }
    toggle.setAttribute('aria-expanded', !isOpen);
  });
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (!dropdown.contains(e.target)) {
      menu.classList.remove('risb-animate-in');
      setTimeout(function() {
        menu.style.display = 'none';
      }, 280);
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
  
  // Handle dropdown action selection
  menu.querySelectorAll('.risb-dropdown-action').forEach(function(btn) {
    btn.addEventListener('click', function() {
      menu.querySelectorAll('.risb-dropdown-action').forEach(function(b) { 
        b.classList.remove('selected'); 
      });
      btn.classList.add('selected');
      input.value = btn.getAttribute('data-value');
      label.innerHTML = btn.innerHTML;
      
      // Close dropdown
      menu.classList.remove('risb-animate-in');
      setTimeout(function() {
        menu.style.display = 'none';
      }, 280);
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
}

// Collapsible panels for mobile
if (window.innerWidth < 700) {
  document.querySelectorAll('.risb-card').forEach(function(card) {
    var header = card.querySelector('.risb-card-title');
    if (!header) return;
    var content = document.createElement('div');
    while (header.nextSibling) {
      content.appendChild(header.nextSibling);
    }
    content.className = 'risb-collapsible-content';
    var btn = document.createElement('button');
    btn.className = 'risb-collapsible-header';
    btn.innerHTML = header.innerHTML + ' <span class="risb-collapsible-arrow">\u25b6</span>';
    var wrapper = document.createElement('div');
    wrapper.className = 'risb-collapsible';
    wrapper.appendChild(btn);
    wrapper.appendChild(content);
    card.appendChild(wrapper);
    header.remove();
    btn.addEventListener('click', function() {
      wrapper.classList.toggle('open');
    });
  });
}

// Add CSS for animation classes
(function() {
  var style = document.createElement('style');
  style.innerHTML = `
    .risb-animate-in {
      animation: risb-fadein 0.35s cubic-bezier(.4,2,.6,1);
    }
    @keyframes risb-fadein {
      from { opacity: 0; transform: translateY(-8px) scale(0.98); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
  `;
  document.head.appendChild(style);
})();

(function($) {
	$(function() {
		var $form = $('form[data-fwc-sat-settings=form]'),
			$actionsOptions = $form.find('[data-fwc-sat-settings=action]'),
			$defaultOptions = $form.find('[data-fwc-sat-settings=default]');

		$actionsOptions.change(function() {
			updateDefaultOptions($defaultOptions, $actionsOptions);
		}).change();
	});

	function updateDefaultOptions($defaultOptions, $actionsOptions) {
		$actionsOptions.each(function(i, elem) {
			var $action = $(elem),
				action = $action.data('fwc-sat-settings-value'),
				$default = $defaultOptions.filter('[value="' + action + '"]');

			if (!$action.prop('checked') && $default.prop('checked')) {
				$defaultOptions.filter('[value="_last"]').prop('checked', true);
			}

			$default.prop('disabled', !$action.prop('checked'));
		});
	}
})(jQuery); 