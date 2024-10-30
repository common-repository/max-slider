!(function ($) {
    "use strict";
    
    const url = window.location.origin + window.location.pathname;

    function maxSliderSlideSetId() {
        $('.elementor-control-slides_repeater .elementor-repeater-fields, .elementor-control-query_select_slide').each(function () {
            let SlideId
            $(this).find('[data-select2-id]:selected').each(function () {
                SlideId = $(this).val();
                console.log(SlideId);
            });
            $(this).find('[data-event="maxSlideEditor"]').each(function () {
                $(this).attr('data-max-slider-slide-id', SlideId);
            });
        });
    }

    function createMaxSliderSlide(postName) {
        console.log(postName);

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            dataType: 'json',
            data: {
                action: 'max_slider_create_slide',
                post_title: postName
            },
            beforeSend: function ()
            {
                console.log('sending');
            },
            success: function(data)
            {
                console.log(data);
                $(".max-slider-editor").remove();
                $('#elementor-editor-wrapper').append(
                    maxSliderEditSlideView(url, data)
                );

            },
            error: function()
            {
                console.log('nay');

            }
        })
    }

    function maxSliderAddSlideView(){
        return  `<div class="max-slider-editor">
            <div class="editor-container">
                <div class="editor-controllers">
                    <i class="eicon-editor-close close-max-slider-editor"></i>
                </div>
                <div class="max-slider-add-slide">
                    <h2 class="max-slider-add-slide-title">Add new slide</h2>
                    <form class="max-slider-add-slide-form" method="POST">
                        <input placeholder="Slide Title" id="max-slider-slide-name" type="text" name="max-slider-slide-name" />
                        <input id="add-max-slider-slide" type="button" value="Add"/>
                    </form>
                </div>
            </div>
        </div>`
    }

    function maxSliderEditSlideView(url, slideId) {
        return `<div class="max-slider-editor">
            <div class="editor-container">
                <div class="editor-controllers">
                    <i class="eicon-editor-close close-max-slider-editor"></i>
                </div>
                <iframe class="editor-view" src="${url}?post=${slideId}&action=elementor"></iframe>
            </div>
        </div>`
    }

    $(document).ready(function () {

        elementor.hooks.addAction('panel/open_editor/widget/max-slider', function (panel, model, view) {
            let $this = $(panel.$el);
            maxSliderSlideSetId();
    
            $this.on('change', '.max-slider-select2', function () {
                maxSliderSlideSetId();
            });

            $this.find('[data-event="maxSliderAddSlide"]').on('click', function () {
                $('#elementor-editor-wrapper').append(
                    maxSliderAddSlideView()
                );
            });
    
            $this.find('[data-event="maxSlideEditor"]').on('click', function () {
                maxSliderSlideSetId();
                const slideId = $(this).attr('data-max-slider-slide-id');
                $('#elementor-editor-wrapper').append(
                    maxSliderEditSlideView(url, slideId)
                );
            });
    
            $('#elementor-editor-wrapper').off('click', '.close-max-slider-editor').on('click', '.close-max-slider-editor', function () {
                let c = 1;
                console.log(c+c);
                $(".max-slider-editor").remove();
                elementor.reloadPreview();
            });

            $('#elementor-editor-wrapper').off('click', '.max-slider-add-slide #add-max-slider-slide').on('click', '.max-slider-add-slide #add-max-slider-slide', function () {
                const postName = $('#max-slider-slide-name').val();
                createMaxSliderSlide(postName);
            });
        });
        
    });

})(jQuery); 