!function($) {
    $(document).ready(function() {
        var popover_form = function(relation, additional_fields) {
            var html =
            '<form method="post" action="/save">' +
            '<fieldset>' +
            '<label>' + relation + ' name</label>' +
            '<input type="text" placeholder="Name" name="name">' +
            '<select name="relation">' +
            '<option value="Brother">Brother</value>' +
            '<option value="Sister">Sister</value>' +
            '<option value="Son">Son</value>' +
            '<option value="Daughter">Daughter</value>' +
            '<option value="Wife">Wife</value>' +
            '<option value="Husband">Husband</value>' +
            '</select>' +
            '<label>Birth date</label>' +
            '<input type="text" name="birth_date">' +
            '<label>Birth place</label>' +
            '<textarea name="birth_place"></textarea>' +
            additional_fields +
            '<div class="form-actions">' +
            '<button type="submit" class="btn update-node">Submit</button>' +
            '</div>' +
            '</fieldset>' +
            '</form>';

            return html;
        };

        $("#family-tree-list").jOrgChart({
            chartElement : '#family-tree-chart',
            dragAndDrop: true,
            nodeOnCreatedCallback: function($nodeDiv, opts) {
                var profile = $('.profile', $nodeDiv),
                    gender = 'male';

                if (profile.hasClass('female')) {
                    gender = 'female';
                }
                $nodeDiv.addClass(gender);
            },
            nodeOnMouseover  : function(e, opts) {
            },
            nodeOnMouseout: function(e, opts) {
            },
            nodeOnClick: function(e, opts) {
            },
            nodeOnDropped: function(e, opts) {
                $('.arrow').on('click', arrowOnClick);
            }
        });

        $('#family-tree-chart').on('mouseover', '.arrow', function() {
            var $node = $(this).parent();

            $('.arrow', $node).addClass('active');

        }).on('mouseout', '.arrow', function() {
            var $node = $(this).parent();

            $('.arrow', $node).removeClass('active');

        });


        var arrowOnClick = function(e) {
            var $node = $(this).parent(),
                currentZIndex = $node.css('z-index'),
                direction,
                html_form,
                additional_fields = '';

            // normalize all zindex
            $('.node').css({'z-index': 10});

            $node.css({'z-index': currentZIndex + 1});

            // Destroy any open popovers
            $('.arrow', '.node').popover('destroy');

            // Which arrow direction
            if ( $(this).hasClass('arrow-left') || $(this).hasClass('arrow-right') ) {
                direction = 'sibling';
            } else if ( $(this).hasClass('arrow-down') ) {
                direction = 'child';
            } else {
                direction = 'parent';
            }

            // additional fields
            additional_fields = $('.fields-to-expose', $node).html();

            // html form
            html_form = popover_form(direction, additional_fields);

            // Open current popover
            $(this).popover({
                html: true,
                title: 'Add ' + direction + ' node',
                content: html_form
            });
            $(this).popover('show');

            e.preventDefault();
        }
        $('.arrow').on('click', arrowOnClick);


        $('.update-node').on('click', function() {
            return false;
        })

        // $(document).on('click', function(e) {
        //     var $el = $(e.target);

        //     // We don't want to close the popover form
        //     // during editing
        //     if ( $el.is('input') ) return true;
        //     if ( $el.hasClass('popover') || $el.parent().hasClass('popover') ) return true;
        //     if ( $el.is('form') || $el.is('fieldset') ) return true;
        //     if ( $el.hasClass('update-node') ) return true;

        //     // destroy any open popover
        //     $('.arrow').popover('destroy');
        // });

        // $('.popover').on('click', function(e) {
        //     e.preventDefault();
        //     e.stopPropagation();

        //     return false;
        // })
    });
}(window.jQuery);
