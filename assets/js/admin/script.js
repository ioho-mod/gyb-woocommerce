function removeURLParameters(removeParams) {
	const deleteRegex = new RegExp(removeParams.join("=|") + "=")

	const params = location.search.slice(1).split("&")
	let search = []
	for (let i = 0; i < params.length; i++) if (deleteRegex.test(params[i]) === false) search.push(params[i])

	window.history.replaceState({}, document.title, location.pathname + (search.length ? "?" + search.join("&") : "") + location.hash)
}
removeURLParameters(["gybevent", "gybnonce"]);
function getyourbill_set_visible_by(type, start){
	var el = getyourbill_all[type];
	for (x in el) {
		if( document.getElementById(x+"_field") != null ){
			document.getElementById(x+"_field").parentNode.parentNode.style.display = ( el[x] || el[x] == "se" || el[x] == "opz")?"table-row":"none";
			if( start && ( !el[x] || el[x] != "se" || el[x] == "opz") ){
				 document.getElementById(x).value = "";
			}
		}
	}
}
(function($){
    $( document ).ready(function() {
      $("#billing_user_type").on("change", function(){
					edittextinlabel('');
      });
			function edittextinlabel(text){
			  $( ".order_data_column .form-field label" ).each(function( index ) {
			    $(this).html($(this).html().replace("{{dinamyctype}}", text));
			  });
			  $( "notice notice-error p" ).each(function( index ) {
			    $(this).html($(this).html().replace("{{dinamyctype}}", text));
			  });
			}
			edittextinlabel('');
      setTimeout(function(){
				edittextinlabel('');
      }, 250);
    });
}(jQuery));

function getyourbill_set_visible_by_order(type, start){
	var el = getyourbill_all[type];

	for (x in el) {
		if( document.getElementById("_billing_"+x) != null ){
			document.getElementById("_billing_"+x).parentNode.style.display = ( el[x] || el[x] == "se" || el[x] == "opz")?"table-row":"none";
			if( document.getElementById("view_p_billing_"+x) != null ){
				document.getElementById("view_p_billing_"+x).style.display = ( el[x] || el[x] == "se" || el[x] == "opz")?"block":"none";
			}
			if( el[x] || el[x] == "se" || el[x] == "opz"){
				document.getElementById("_billing_"+x).setAttribute("name", x);
			}else{
				document.getElementById("_billing_"+x).removeAttribute("name");
			}
			if( start && ( !el[x] || el[x] != "se" || el[x] == "opz") ){
				 document.getElementById("_billing_"+x).value = "";
			}
		}
	}
	if( type === "pa" ){
		if( !start ){
			jQuery("#_billing_country").val("IT").change();
		}
	}
}
function getyourbill_set_sdiid(val, modes){

	 if( val == "IT" || val == "SM" ){
		 	 document.getElementById("_billing_billing_sdiid").value = document.getElementById("_billing_billing_sdiid").getAttribute("preval");
		 	 document.getElementById("_billing_billing_sdiid").removeAttribute("disabled");
	 }else{
	 	 document.getElementById("_billing_billing_sdiid").setAttribute("preval", document.getElementById("_billing_billing_sdiid").value);
		 document.getElementById("_billing_billing_sdiid").setAttribute("disabled", true);
		 document.getElementById("_billing_billing_sdiid").value = "XXXXXXX";
	 }
}
function getyourbill_sel_user_tipe_event(){
	if( document.getElementById("billing_user_type") != null ){
		document.getElementById("billing_user_type").addEventListener("change", e => {  getyourbill_set_visible_by_order(e.target.value, true);});

		getyourbill_set_visible_by_order( getyourbill_userinfo.billing_user_type, false);
		document.getElementById('billing_user_type').querySelector('option[value='+getyourbill_userinfo.billing_user_type+']').selected = 'selected';

		var el = getyourbill_all[getyourbill_userinfo.billing_user_type];
		for (x in el) {
			if( document.getElementById("_billing_"+x) != null ){
				if( getyourbill_userinfo[x] != undefined &&  (el[x] || el[x] == "se" || el[x] == "opz")){
					 document.getElementById("_billing_"+x).value = getyourbill_userinfo[x];
				}
			}
		}

		document.getElementById("_billing_country").addEventListener("change", e => {  getyourbill_set_sdiid(e.target.value, true); });
	//	getyourbill_set_sdiid(document.getElementById("_billing_country").value, false);
	}
}
getyourbill_sel_user_tipe_event();
if( document.getElementById("billing_user_type") != null ){
	document.getElementById("billing_user_type").addEventListener("change", e => {  getyourbill_set_visible_by(e.target.value, true);});
	getyourbill_set_visible_by(document.getElementById("billing_user_type").value, false);
}else{
	if( document.getElementById("billing_user_type_psoleproprietor") != null ){
		for (x in getyourbill_all) { document.getElementById("billing_user_type_"+x).addEventListener("change", e => {  getyourbill_set_visible_by(e.target.value, true); }); }
		var getyourbill_v = document.querySelector('input[name="billing_user_type"]:checked');
		if( getyourbill_v != null ){
			getyourbill_set_visible_by(getyourbill_v.value), false;
		}else{
			getyourbill_set_visible_by(document.getElementById("billing_user_type_psoleproprietor").value, false);
		}
	}
}
jQuery('select[name="customer_user"]').on("change", function(e){
		jQuery.ajax({
			url: getyourbill_script.ajax,
			dataType: "json",
			data: {
				action  : "getyourbill_get_campi_by_user",
				getyourbill_nonce:  getyourbill_script.nonce,
				id: jQuery(this).val(),
			},
			type: "GET",
			success: function( response ) {
				if ( response.success ) {
					jQuery(".address").css("display","none");
					jQuery(".edit_address").css("display","block");
					setTimeout(function () {
						document.getElementById("billing_user_type").value = response.success;
						getyourbill_set_visible_by_order(response.success, false);
					}, 250);
				}
			}
		});
});



function get_your_bill_tax(){

	jQuery("#gyb_tbogy .gyb_save:not(initaction)").addClass("initaction").on("click", function(e){
		e.stopPropagation();
		var that = this;
		if( jQuery(that).parents("tr.gyb_new_iterm").find("input[name=id]").val() === "0" && jQuery(that).parents("tr.gyb_new_iterm").find("select[name=gyb_tax]").val() === "-1" ){
			alert( getyourbill_traslate.select_tax_before_save );
		}else{
			jQuery(that).parent().parent().addClass("load");
			jQuery.ajax({
				url: getyourbill_script.ajax,
				dataType: "json",
				data: {
					action  : "getyourbill_add_tax",
					getyourbill_nonce:  getyourbill_script.nonce,
					data: jQuery(that).parents("tr.gyb_new_iterm").find("input,select").serialize(),
				},
				type: "POST",
				success: function( response ) {
					jQuery(that).parent().parent().removeClass("load");
					if( response.success ){
						jQuery(that).parents("tr.gyb_new_iterm").find("select[name=gyb_tax]").html("");
						jQuery(that).parents("tr.gyb_new_iterm").find("span[type=gyb_tax]").html(response.title_gyb_tax);
						if ( response.action === "add" ) {
							jQuery(that).parents("tr.gyb_new_iterm").find("input[name=id]").val(response.item);
							if( response.item === 0){
								jQuery(that).parents("tr.gyb_new_iterm").addClass("newel");
								jQuery(that).parents("tr.gyb_new_iterm").find("select[name=gyb_tax]").html( jQuery("#gyb_selects select[name=gyb_tax]").clone().html() );
							}else{
								jQuery(that).parents("tr.gyb_new_iterm").removeClass("editmod").removeClass("newel");
							}
							jQuery(that).parents("tr.gyb_new_iterm").attr("data-tax", response.tax);
						}
					}
					jQuery(that).parents("tr.gyb_new_iterm").attr("data-gyb_tax", response.gyb_tax);
					jQuery(that).parents("tr.gyb_new_iterm").removeClass("editmod").removeClass("newel");
				},
				failed: function( response ) {
					alert("Riprovare");
					jQuery(that).parent().parent().removeClass("load");
				}
			});
		}
	});
	jQuery("#gyb_tbogy .gyb_edit:not(initaction)").addClass("initaction").on("click", function(e){
		e.stopPropagation();
		var that = this;
		jQuery(that).parents("tr.gyb_new_iterm").find("select[name=gyb_tax]").html( jQuery("#gyb_selects select[name=gyb_tax]").clone().html() );
		jQuery(that).parents("tr.gyb_new_iterm").find("select[name=gyb_tax]").find("option[value='"+jQuery(that).parents("tr.gyb_new_iterm").attr("data-gyb_tax")+"']").prop("selected", true);
		jQuery(this).parents("tr.gyb_new_iterm").addClass("editmod");
	});
	jQuery("#gyb_tbogy .gyb_annula:not(initaction)").addClass("initaction").on("click", function(e){
		e.stopPropagation();
		if( jQuery(this).parents("tr.gyb_new_iterm").hasClass("newel") ){

		}else{
			jQuery(this).parents("tr.gyb_new_iterm").removeClass("editmod");
			jQuery(this).parents("tr.gyb_new_iterm").find("select[name=gyb_tax]").html("");
		}
	});
	jQuery("#gyb_tbogy select[name=gyb_tax]:not(initaction)").addClass("initaction").on("change", function(e){
		window.onbeforeunload = null;
	});
}
function get_your_bill_tax_element(item, mod){
	return '<tr class=" gyb_new_iterm '+mod+'" data-tax="'+item.tax+'"  data-gyb_tax="'+item.gyb_tax+'"><td class="space" width="0.5%"></td><td class="name" width=""><span class="" type="tax">'+item.title_tax+'</span><div class="editmod_tax"><input type="hidden" name="tax" value="'+item.tax+'"></div></td><td class="status" width=""><span class="gyb-method-title" type="gyb_tax">'+item.title_gyb_tax+'</span><div class="editmod_gyb_tax"><select name="gyb_tax">'+jQuery("#gyb_selects select[name=gyb_tax]").clone().html()+'</select></div></td><td class="action" style="width:192px"><input type="hidden" name="id" value="'+item.id+'"/><a class="button alignright gyb_edit" href="#edit">'+getyourbill_traslate.gestisci+'</a><a class="button alignright gyb_annula" href="#annulla">'+getyourbill_traslate.annulla+'</a><a class="button alignright gyb_action gyb_save" href="#edit">'+getyourbill_traslate.salva+'</a></td></tr>';
}

var gyb_addrow = jQuery("#gyb_tbogy");
if( gyb_addrow.length > 0 ){

	jQuery.each(getyorbill_all_tax, function(i, item) {
		jQuery("#gyb_tbogy").append(get_your_bill_tax_element(item, (item.edit?"editmod newel":"")));
	});
	get_your_bill_tax();
}
