
jQuery( document ).ready(function($) {
	function getyourbill_set_visible_by_front(type, start, editcountry){
		type = (type=="")?"nessuno":type;
		document.getElementsByTagName("BODY")[0].setAttribute("gtype", type);
		if( type != "nessuno" && typeof type !== undefined){
			var el = getyourbill_all[type];

			if( el !== undefined ){
				for (let x in el) {

					if( document.getElementById(x+"_field") != null ){
						document.getElementById(x+"_field").classList.remove("validate-required");
						document.getElementById(x+"_field").style.display = ( el[x] || el[x] == "se" || el[x] == "opz" )?"block":"none";

						if( getyourbill_all_validate[type].hasOwnProperty(x) ){
							if( getyourbill_all_validate[type][x] ){

								document.getElementById(x+"_field").classList.add("validate-required");
							}
						}
						if( type != "nessuno" ){
							if( getyourbill_all_desc[type].hasOwnProperty(x) ){
								var gyb_option = document.getElementById(x+"_field").getElementsByClassName("optional");
								var gyb_abbr = document.getElementById(x+"_field").getElementsByTagName("abbr");
								var gyb_label = document.getElementById(x+"_field").getElementsByTagName("label");
								if( gyb_option.length >0 ){
									gyb_option[0].remove();
									gyb_label[0].insertAdjacentHTML('beforeend', getyourbill_all_desc[type][x])
								}else if( gyb_abbr.length >0 ){
									gyb_abbr[0].remove();
									gyb_label[0].insertAdjacentHTML('beforeend',  getyourbill_all_desc[type][x])
								}
							}
						}

						if( x == "billing_pi" ){
							if( document.getElementById(x+"_field") !== null ){
								var gyb_billing_pi = document.getElementById(x+"_field").getElementsByTagName("label")[0].getElementsByTagName("abbr")[0];
								if( typeof gyb_billing_pi !== "undefined" ){
									gyb_billing_pi.removeAttribute("class");
									if( el[x] == true ){
										gyb_billing_pi.classList.add("required");
										gyb_billing_pi.innerHTML = "*";
									}else{
										gyb_billing_pi.classList.add("optional");
										gyb_billing_pi.innerHTML = " ( "+getyourbill_traslate .opzional+" ) ";
									}
								}
							}
						}
					}
				}
				if( editcountry && type === "pa" ){
					$("#billing_country").val("IT").change();
				}
			}
		}else{
			var el = getyourbill_all["psoleproprietor"];
			for (x in el) {
				if( document.getElementById(x+"_field") != null ){
					document.getElementById(x+"_field").style.display = "none";
				}
			}
		}
	}
	var gyb_prepare_typeuser_front_time = null;
	function gyb_prepare_typeuser_front(){
		if( document.getElementById("billing_user_type") != null ){
			document.getElementById("billing_user_type").addEventListener("change", e => {
				getyourbill_set_visible_by_front(e.target.value, true, true);
				$('.gyb_autof_input').remove();
				gyb_set_field(e.target.value,gyb_field, false);
				jQuery(document.body).trigger("update_checkout");
			});
			clearTimeout(gyb_prepare_typeuser_front_time);
			gyb_prepare_typeuser_front_time = setTimeout(function(){
				getyourbill_set_visible_by_front(document.getElementById("billing_user_type").value, getyourbill_is_r, false);
			}, 250);
		}else{
			if( document.getElementById("billing_user_type_psoleproprietor") != null ){
				for (x in getyourbill_all) {
					var item = document.getElementById("billing_user_type_"+x);
					if( item !== null ){
						item.addEventListener("change", e => {
							getyourbill_set_visible_by_front(e.target.value, true, false);
							$('.gyb_autof_input').remove();
							gyb_set_field(e.target.value,gyb_field, false);
						});
					}
				}
				clearTimeout(gyb_prepare_typeuser_front_time);
				gyb_prepare_typeuser_front_time = setTimeout(function(){
					var getyourbill_v = document.querySelector('input[name="billing_user_type"]:checked');
					if( getyourbill_v != null ){
						getyourbill_set_visible_by_front(getyourbill_v.value, getyourbill_is_r, false);
					}else{
						getyourbill_set_visible_by_front("nessuno", getyourbill_is_r, false);
					}
				}, 250);
			}
		}
	}
	gyb_prepare_typeuser_front();

	var gyb_field = null;
	var elmg = $(".getyourbill_autocomlete_content_in .getyourbill_info");
	var mod = "";
	var val = "";

	function gyb_set_field(type, gyb_field_, hiddenmod){
		if( type != "nessuno" && typeof type !== undefined){
			var el = getyourbill_all[type];
			if( gyb_field_ != null && typeof gyb_field_ !== undefined){
				$.each(gyb_field_.success, function(i, item) {
					if( gyb_field_.data_refer.hasOwnProperty(i) ){
						if( $("#"+gyb_field_.data_refer[i].name).length ){

							if( el !== undefined ){
								if( el.hasOwnProperty(gyb_field_.data_refer[i].name) ){
									if( ( el[gyb_field_.data_refer[i].name] || el[gyb_field_.data_refer[i].name] == "se" || el[gyb_field_.data_refer[i].name] == "opz") ){
										$("#"+gyb_field_.data_refer[i].name).val(item);
									}else{
										$("#"+gyb_field_.data_refer[i].name).val("");
									}
								}else{
									$("#"+gyb_field_.data_refer[i].name).val(item);
								}
							}else{
								$("#"+gyb_field_.data_refer[i].name).val(item);
								/*
								* gestisce la casisitica se il select è custom di WooCommerce
								* */
								if ($("#"+gyb_field_.data_refer[i].name).prop("type") === 'select-one'){
									$("#"+gyb_field_.data_refer[i].name).trigger('change');
								}
							}

						}else{
							if( gyb_field_.data_refer[i].name != "" ){
								if( $('form[name="checkout"]').length ){
									$('form[name="checkout"]').append('<input class="gyb_autof_input" type="hidden" name="autof_'+i+'" value="'+item+'"/>');
								}else if( $("#get_order_fields").length ){
									$("#get_order_fields").prepend('<input class="gyb_autof_input" type="hidden" name="autof_'+i+'" value="'+item+'" />');
								}
							}
						}
					}
				});
			}
		}
	}
	$("#getyourbill_autocomlete_close, #getyourbill_autocomlete_icon_close, .getyourbill_overlay").on("click", function(e){
		$("#getyourbill_autocomlete").css("display", "none");
		err_h.removeClass("error").removeClass("success");
		err_h.html("");
		err_h_front.removeClass("error").removeClass("success");
		err_h_front.html("");
		if( $("#customer_details").length ){
			$("html, body").animate({ scrollTop: $("#customer_details").offset().top }, "slow");
		}
	});
	$("#billing_country").on("change", function(event){
		getyourbill_count_sdiid($(this).val());
	});
	function getyourbill_count_sdiid(val){
		if( val === "IT" || val === "SM"){
			$("#billing_sdiid").prop("disabled", false).val('');
			$("label[for=billing_cf]").find("abbr").removeAttr( 'style' );
			$('#billing_sdiid_field').removeClass('hidden')
			if($("#billing_billing_sdiid_hidden").length){$("#billing_billing_sdiid_hidden").remove();}
		}else{
			$("#billing_sdiid").attr("preval",  $("#billing_sdiid").val());
			$("#billing_sdiid").prop("disabled", true).val("XXXXXXX");
			$("label[for=billing_cf]").find("abbr").css("display", "none");
			if($("#billing_billing_sdiid_hidden").length === 0){
				var billinkfomr = document.querySelector("form[name='checkout']");
				if( billinkfomr ){
					var input = document.createElement("input");
					input.type = "hidden";
					input.name = "billing_sdiid";
					input.value = "XXXXXXX" ;
					input.id = "billing_billing_sdiid_hidden";
					billinkfomr.appendChild(input);
				}
			}
		}
	}
	getyourbill_count_sdiid($("#billing_country").val());

	jQuery( document.body ).on('updated_checkout', function(){
		// getyourbill_set_visible_by_front(jQuery('#billing_user_type').val(), true, false);
		// gyb_set_field(jQuery('#billing_user_type').val(),gyb_field, false);
	});

	// $("#getyourbill_autocomlete .getyourbill_overlay").on("click", function(event){
	// 	if (!$(event.target).closest('.getyourbill_autocomlete_content').length) {
	// 		$("#getyourbill_autocomlete").css("display", "none");
	// 		err_h.removeClass("error").removeClass("success");
	// 		err_h.html("");
	// 		err_h_front.removeClass("error").removeClass("success");
	// 		err_h_front.html("");
	// 		if( $("#customer_details").length ){
	// 			$("html, body").animate({ scrollTop: $("#customer_details").offset().top }, "slow");
	// 		}
	// 	}
	// });

	$( window ).load(function() {
		if( $('.woocommerce-EditAccountForm #password_current').length ){
			$('.woocommerce-EditAccountForm input#password_current').attr("autocomplete", "new-password");
			$('.woocommerce-EditAccountForm input#password_current').val("");
			setTimeout(function(){
				$('.woocommerce-EditAccountForm input#password_current').attr("autocomplete", "new-password");
				$('.woocommerce-EditAccountForm input#password_current').val("");
			}, 500);
		}
		getyourbill_count_sdiid($("#billing_country").val());
	});

	if( $("#popgybfields").length ){
		$("#popgybfields").on("change", function(event){
			event.stopPropagation();
			event.preventDefault();
			$(".getyourbill_info").val("");
			$(this).parents(".getyourbill_autocomlete_content").attr("mods", $(this).val() );
			$("#billing_user_type").val( $("#popgybfields").val() );
			getyourbill_set_visible_by_front( $("#popgybfields").val(), true, true);
		});
		$("#popgybfields").parents(".getyourbill_autocomlete_content").attr("mods", $("#popgybfields").val() );

		$(".getyourbill_autocomlete .getyourbill_info").on("input", function(event){
			if( $("#billing_user_type").val() != $("#popgybfields").val() ){
				$("#billing_user_type").val( $("#popgybfields").val() );
			}
		});

	}

	$("#getyourbill_view_autocomlete").on("click", function(e){
		err_h.removeClass("error").removeClass("success");
		err_h.html("");
		err_h_front.removeClass("error").removeClass("success");
		err_h_front.html("");
		$("#getyourbill_autocomlete").css("display", "flex");


		let valore_input = '';
		if ( $('input[name=billing_user_type]') !== undefined ){
			valore_input = $('input[name=billing_user_type]').val();
		}

		$("#popgybfields").val( valore_input ).change();

		// if( $("#billing_user_type").val() == "" ){
		// 	$("#popgybfields").val( $("#popgybfields option").first().val() );
		// }else{
		// 	$("#popgybfields").val( $("#billing_user_type").val() );
		// }
	});

	gyb_set_field(jQuery('input[name="billing_user_type"]:checked').val(), gyb_field, getyourbill_is_r);
	var err_h = $("#getyourbill_autocomlete_content_response");
	var err_h_front = $("#getyourbill_autocomlete_content_response_front");
	$("#getyourbill_autocomlete_cerca").on("click", function(event){
		event.preventDefault();
		gyb_cerca_web();
	});
	$("#billing_user_type").on("change", function(event){
		$(".getyourbill_autocomlete_content_in").removeClass("psoleproprietor pperson pcompany pa");
		$(".getyourbill_autocomlete_content_in").find(".getyourbill_info").val("");
		$(".getyourbill_autocomlete_content_in").addClass($(this).val());
	});
	$("#getyourbill_autocomlete_cerca input").on("change", function(event){
		event.stopPropagation();
		event.preventDefault();
		gyb_cerca_web();
	});
	if( $("ul.woocommerce-error li").length == 0 && getyourbill_is_r ){
		$("#getyourbill_view_autocomlete").click();
	}

	if( $("#billing_update_info_on_checkout").length > 0 && !getyourbill_is_r ){
		$("#billing_update_info_on_checkout").prop( "checked", false );
	}

	function gyb_cerca_web(){
		let input = {"action"  : "getyourbill_info", "getyourbill_nonce":  getyourbill_script.nonce, "pi":"", "cf":"", "bilid": "", "pa": ""};

		elmg.each(function( index ) {
			input[$(this).attr("mod")] = $(this).val();
		});


		if( input.pi == '' && input.cf == '' && input.bilid == '' && input.pa == ''  ){
			$("#getyourbill_autocomlete").css("display", "none");
			if( $("#customer_details").length ){
				$("html, body").animate({ scrollTop: $("#customer_details").offset().top }, "slow");
			}
			return;
		}

		$(".getyourbill_autocomlete_content_in").addClass("gload");
		err_h.removeClass("error").removeClass("success");
		err_h.html(getyourbill_messagges.loading);
		err_h_front.removeClass("error").removeClass("success");
		err_h_front.html("");

		if( jQuery('input.getyourbill_info[mod="pi"]').val().trim() != "" ){
			if( jQuery("#billing_user_type_pperson").length ){
				jQuery("#billing_user_type_pperson").prop("checked", true);
			}else{
				jQuery('#billing_user_type').val("pperson");
			}
		}else if( jQuery('input.getyourbill_info[mod="cf"]').val().trim() != "" ){
			if( jQuery("#billing_user_type_psoleproprietor").length ){
				jQuery("#billing_user_type_psoleproprietor").prop("checked", true);
			}else{
				jQuery('#billing_user_type').val("psoleproprietor");
			}
		}


		if ($('input[name="billing_user_type"]').length > 0){
			$('input#billing_user_type_' + $("#popgybfields").val()).attr('checked', true);
		}
		else if($('select#billing_user_type').length > 0){
			$('select#billing_user_type').val( $("#popgybfields").val() ).change()
		}

		jQuery.ajax({
			url: getyourbill_script.ajax,
			dataType: "json",
			data: input,
			type: "GET",
			success: function( response ) {
				$(".getyourbill_autocomlete_content_in").removeClass("gload");
				$('.gyb_autof_input').remove();
				if ( response.success ) {
					gyb_field = response;

					gyb_set_field($('input[name="billing_user_type"]:checked').val(), gyb_field, false);
					if( $('form[name="checkout"]').length ){
						$('.gyb_autof_getyourbill_auto').remove();
						$('form[name="checkout"]').append('<input class="gyb_autof_getyourbill_auto" type="hidden" name="getyourbill_auto" value="auto"/>');
					}else if( $("#get_order_fields").length ){
						$('.gyb_autof_getyourbill_auto').remove();
						$('#get_order_fields').append('<input class="gyb_autof_getyourbill_auto" type="hidden" name="getyourbill_auto" value="auto"/>');
					}
					err_h.addClass("success");
					err_h.html(response.mess);
					err_h_front.addClass("success");
					err_h_front.html(response.mess);
				}else{
					if( response.error ){
						err_h.addClass("error");
						err_h.html(response.mess);
						err_h_front.html(response.mess);
					}else{
						err_h.addClass("error");
						err_h.html(getyourbill_messagges.error_res);
					}
				}
				if( $("#customer_details").length ){
					$("html, body").animate({ scrollTop: $("#customer_details").offset().top }, "slow");
				}
				$("#getyourbill_autocomlete").css("display", "none");

				getyourbill_set_visible_by_front($('#billing_user_type').val(), true, true);
			},
			error: function( response ) {
				err_h.addClass("error");
				err_h.html(getyourbill_messagges.error_ajax);
				$(".getyourbill_autocomlete_content_in").removeClass("gload");
			}
		});
	}
});
