// Improved Save Button Classic Editor integration
window.FastWebCreations = window.FastWebCreations || {};
window.FastWebCreations.SaveAndThen = window.FastWebCreations.SaveAndThen || {};

(function($) {
    var SAT = window.FastWebCreations.SaveAndThen;

    $(function() {
        var config = SAT.config,
            $form = $('#post');

        if (config && $form.length) {
            new SAT.PostEditForm($form, config);
        }
    });

    /**
     * Main class that handles the post edit form
     */
    SAT.PostEditForm = function($form, config) {
        if (!config.actions || config.actions.length === 0) {
            return;
        }

        this.$form = $form;
        this.config = config;
        this.action = null;

        var defaultAction = this.getDefaultAction();

        this.$actionInput = this.createActionInput();
        this.$originalPublishButton = this.getOriginalPublishButton();
        this.newPublishButtonSet = new SAT.PublishButtonSet(this);

        this.setupForm();
        this.setupOriginalPublishButton();
        this.newPublishButtonSet.setAction(defaultAction);
        this.insertNewPublishButtonSet();
    };

    SAT.PostEditForm.prototype = {
        getOriginalPublishButton: function() {
            return this.$form.find('#publish');
        },

        createActionInput: function() {
            return $('<input type="hidden" name="' + SAT.HTTP_PARAM_ACTION + '" />');
        },

        setupForm: function() {
            this.$form.prepend(this.$actionInput);
        },

        setupOriginalPublishButton: function() {
            if (this.config.setAsDefault) {
                this.$originalPublishButton
                    .removeClass('button-primary')
                    .removeAttr('accesskey');
            }
        },

        insertNewPublishButtonSet: function() {
            var $container = this.newPublishButtonSet.$container;
            
            if (this.config.setAsDefault) {
                this.$originalPublishButton.after($container);
            } else {
                this.$originalPublishButton.before($container);
            }
            // Hide the default Update/Publish button
            this.$originalPublishButton.hide();
        },

        setAction: function(newAction) {
            this.action = newAction;
            this.$actionInput.val(newAction.id);
        },

        getAction: function() {
            return this.action;
        },

        submit: function() {
            this.setAction(this.newPublishButtonSet.getAction());
            
            var customSubmitEvent = $.Event('fwc-sat:submit');
            this.$form.trigger(customSubmitEvent, this);

            if (customSubmitEvent.isDefaultPrevented()) {
                return;
            }

            this.$form.data('fwc-sat-button-submitted', true);
            this.getOriginalPublishButton().click();
        },

        getDefaultAction: function() {
            var defaultActionId = this.config.defaultActionId,
                defaultAction = null,
                fallbackAction;

            $.each(this.config.actions, function(i, action) {
                if (action.enabled) {
                    fallbackAction = action;
                    return false;
                }
            });

            if (!defaultActionId) {
                return fallbackAction;
            }

            defaultAction = this.getActionFromId(defaultActionId);

            if (!defaultAction || !defaultAction.enabled) {
                defaultAction = fallbackAction;
            }

            return defaultAction;
        },

        getActionFromId: function(id) {
            var foundAction = null;

            $.each(this.config.actions, function(i, action) {
                if (action.id === id) {
                    foundAction = action;
                    return false;
                }
            });

            return foundAction;
        }
    };

    /**
     * Class that creates and manages the button set
     */
    SAT.PublishButtonSet = function(postEditForm) {
        this.postEditForm = postEditForm;
        this.config = this.postEditForm.config;
        this.action = null;

        this.$mainButton = this.createMainButton();
        this.$dropdownButton = this.createDropdownButton();
        this.$dropdownMenu = this.createDropdownMenu();
        this.$container = this.createContainer();

        this.setupDocumentClickListener();
        this.setupMainButtonListeners();
        this.setupDropdownButtonListeners();
        this.setupDropdownMenuListeners();
    };

    SAT.PublishButtonSet.prototype = {
        createMainButton: function() {
            var $mainButton = $('<button type="button"/>');
            $mainButton.attr('class', 'button button-large fwc-sat-main-button');

            if (this.config.setAsDefault) {
                $mainButton.addClass('button-primary');
            }

            return $mainButton;
        },

        createDropdownButton: function() {
            var $dropdownButton = $('<input type="button" value="▼" />');
            $dropdownButton.attr('class', this.$mainButton.attr('class'));
            $dropdownButton
                .removeClass('fwc-sat-main-button')
                .addClass('fwc-sat-dropdown-button');

            return $dropdownButton;
        },

        createDropdownMenu: function() {
            var $dropdownMenu = $('<ul class="fwc-sat-dropdown-menu"></ul>'),
                self = this;

            $.each(this.config.actions, function(i, actionData) {
                var $item = $('<li data-fwc-sat-value="' + actionData.id + '">' + self.generateButtonLabel(actionData.buttonLabelPattern) + '</li>');

                if (actionData.title) {
                    $item.attr('title', actionData.title);
                }

                if (actionData.enabled) {
                    $item.data('fwcSatActionData', actionData);
                } else {
                    $item.addClass('disabled');
                }

                $dropdownMenu.append($item);
            });

            return $dropdownMenu;
        },

        createContainer: function() {
            var $container = $('<span class="fwc-sat-container"></span>');

            $container.append(this.$mainButton);

            if (this.config.actions.length > 1) {
                $container
                    .addClass('fwc-sat-with-dropdown')
                    .append(this.$dropdownButton)
                    .append(this.$dropdownMenu);
            }

            if (this.config.setAsDefault) {
                $container.addClass('fwc-sat-set-as-default');
            }

            return $container;
        },

        setupDocumentClickListener: function() {
            var self = this;
            $(document).click(function() {
                if (self.menuShown()) {
                    self.hideMenu();
                }
            });
        },

        setupMainButtonListeners: function() {
            var self = this;
            this.$mainButton.click(function() {
                if ($(this).hasClass('disabled')) {
                    return;
                }
                self.postEditForm.submit();
            });
        },

        setupDropdownButtonListeners: function() {
            var self = this;
            this.$dropdownButton.click(function(event) {
                if (!self.menuShown()) {
                    self.showMenu();
                    event.stopPropagation();
                }
            });
        },

        setupDropdownMenuListeners: function() {
            var self = this;
            this.$dropdownMenu.on('click', 'li', function() {
                if ($(this).hasClass('disabled')) {
                    return;
                }
                self.setAction($(this).data('fwcSatActionData'));
                self.$mainButton.click();
            });
        },

        menuShown: function() {
            return this.$container.hasClass('fwc-sat-dropdown-menu-shown');
        },

        showMenu: function() {
            this.$container.addClass('fwc-sat-dropdown-menu-shown');
        },

        hideMenu: function() {
            this.$container.removeClass('fwc-sat-dropdown-menu-shown');
        },

        setAction: function(action) {
            this.action = action;
            this.updateLabels();
        },

        getAction: function() {
            return this.action;
        },

        updateLabels: function() {
            var self = this;
            this.$mainButton.html(this.generateButtonLabel(this.action.buttonLabelPattern));
            this.$mainButton.attr('title', this.action.title ? this.action.title : '');

            $.each(this.config.actions, function(i, actionData) {
                var $li = self.$dropdownMenu.find('[data-fwc-sat-value="' + actionData.id + '"]');
                $li.html(self.generateButtonLabel(actionData.buttonLabelPattern));
            });
        },

        generateButtonLabel: function(pattern) {
            return pattern.replace('%s', this.postEditForm.$originalPublishButton.val());
        }
    };

})(jQuery); 