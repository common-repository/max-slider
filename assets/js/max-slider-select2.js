(function ($) {
    $(document).on('max_slider_select2_init', function (event, obj) {
        var ID = '#elementor-control-default-' + obj.data._cid;
        setTimeout(function () {
            var IDSelect2 = $(ID).select2({
                minimumInputLength: 3,
                ajax: {
                    type: 'POST',
                    url: max_slider_select2_localize.ajaxurl,
                    dataType: 'json',
                    data: function ( params ) {
                        return {
                            action: 'max_slider_select2_search_post',
                            post_type: obj.data.source_type,
                            source_name: obj.data.source_name,
                            meta_query: obj.data.meta_query,
                            term: params.term,
                        }
                    },
                },
                initSelection: function (element, callback) {
                    if (!obj.multiple) {
                        callback({id: '', text: max_slider_select2_localize.search_text});
                    }else{
						callback({id: '', text: ''});
					}
					var ids = [];
                    if(!Array.isArray(obj.currentID) && obj.currentID != ''){
						 ids = [obj.currentID];
					}else if(Array.isArray(obj.currentID)){
						 ids = obj.currentID.filter(function (el) {
							return el != null;
						})
					}

                    if (ids.length > 0) {
                        var label = $("label[for='elementor-control-default-" + obj.data._cid + "']");
                        label.after('<span class="elementor-control-spinner">&nbsp;<i class="eicon-spinner eicon-animation-spin"></i>&nbsp;</span>');
                        $.ajax({
                            method: "POST",
                            url: max_slider_select2_localize.ajaxurl,
                            data: {
                                action: 'max_slider_select2_get_title',
                                post_type: obj.data.source_type, 
                                source_name: obj.data.source_name, 
                                meta_query: obj.data.meta_query,
                                id: ids
                            }
                        }).done(function (response) {
                            if (response.success && typeof response.data.results != 'undefined') {
                                let maxSliderSelect2Options = '';
                                ids.forEach(function (item, index){
                                    if(typeof response.data.results[item] != 'undefined'){
                                        const key = item;
                                        const value = response.data.results[item];
                                        maxSliderSelect2Options += `<option selected="selected" value="${key}">${value}</option>`;
                                    }
                                })

                                element.append(maxSliderSelect2Options);
                            }
							label.siblings('.elementor-control-spinner').remove();
                        });
                    }
                }
            });

            //Manual Sorting : Select2 drag and drop : starts
            // #ToDo Try to use promise in future
            setTimeout(function (){
                IDSelect2.next().children().children().children().sortable({
                    containment: 'parent',
                    stop: function(event, ui) {
                        ui.item.parent().children('[title]').each(function() {
                            var title = $(this).attr('title');
                            var original = $('option:contains(' + title + ')', IDSelect2).first();
                            original.detach();
                            IDSelect2.append(original)
                        });
                        IDSelect2.change();
                    }
                });

                $(ID).on("select2:select", function(evt) {
                    var element = evt.params.data.element;
                    var $element = $(element);

                    $element.detach();
                    $(this).append($element);
                    $(this).trigger("change");
                });
            },200);
            //Manual Sorting : Select2 drag and drop : ends

        }, 100);

    });
}(jQuery));

function ea_woo_cart_column_type_title(value) {
    const labelValues = {
        remove: max_slider_select2_localize.remove,
        thumbnail: max_slider_select2_localize.thumbnail,
        name: max_slider_select2_localize.name,
        price: max_slider_select2_localize.price,
        quantity: max_slider_select2_localize.quantity,
        subtotal: max_slider_select2_localize.subtotal,
    };

    return labelValues[value] ? labelValues[value] : '';
}

function ea_conditional_logic_type_title(value) {
    const labelValues = {
        login_status: max_slider_select2_localize.cl_login_status,
        user_role: max_slider_select2_localize.cl_user_role,
        user: max_slider_select2_localize.cl_user,
        post_type: max_slider_select2_localize.cl_post_type,
        dynamic: max_slider_select2_localize.cl_dynamic,
        browser: max_slider_select2_localize.cl_browser,
        date_time: max_slider_select2_localize.cl_date_time,
        recurring_day: max_slider_select2_localize.cl_recurring_day,
        query_string: max_slider_select2_localize.cl_query_string,
        visit_count: max_slider_select2_localize.cl_visit_count,
    };

    return labelValues[value] ? labelValues[value] : '';
}