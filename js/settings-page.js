// Modern UI for Really Improved Save Button settings page

document.addEventListener('DOMContentLoaded', function() {
  // Dropdown for default action
  var dropdown = document.getElementById('risb-default-action-dropdown');
  if (dropdown) {
    var toggle = document.getElementById('risb-dropdown-toggle');
    var menu = document.getElementById('risb-dropdown-menu');
    var input = document.getElementById('risb-default-action-input');
    var label = document.getElementById('risb-dropdown-selected-label');
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      if (menu.style.display === 'block') {
        menu.style.display = 'none';
        menu.classList.remove('risb-animate-in');
      } else {
        menu.style.display = 'block';
        // Trigger reflow for animation
        void menu.offsetWidth;
        menu.classList.add('risb-animate-in');
      }
      toggle.setAttribute('aria-expanded', menu.style.display === 'block');
    });
    document.addEventListener('click', function(e) {
      if (!dropdown.contains(e.target)) {
        menu.style.display = 'none';
        menu.classList.remove('risb-animate-in');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
    menu.querySelectorAll('.risb-dropdown-action').forEach(function(btn) {
      btn.addEventListener('click', function() {
        menu.querySelectorAll('.risb-dropdown-action').forEach(function(b) { b.classList.remove('selected'); });
        btn.classList.add('selected');
        input.value = btn.getAttribute('data-value');
        label.innerHTML = btn.innerHTML;
        menu.style.display = 'none';
        menu.classList.remove('risb-animate-in');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  // Animate toggles on change
  document.querySelectorAll('.risb-toggle input[type="checkbox"]').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
      var slider = toggle.nextElementSibling;
      if (slider) {
        slider.classList.remove('risb-animate-in');
        void slider.offsetWidth;
        slider.classList.add('risb-animate-in');
      }
    });
  });

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
});

// Add CSS for animation classes
(function() {
  var style = document.createElement('style');
  style.innerHTML = `
    .risb-animate-in {
      animation: risb-fadein 0.35s cubic-bezier(.4,2,.6,1);
    }
  `;
  document.head.appendChild(style);
})(); 