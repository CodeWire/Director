jQuery(document).ready( function($) {
	$(document).on('change','#isCat',function(){
		var ids = $(this).val();
		var droptext = $("#isCat option:selected").text();
		$('#hiddenIscat').val('');
		$('#hiddenIscat').val(droptext);
		$('#hiddenIscatid').val('');
		$('#hiddenIscatid').val(ids);
		$.ajax({
			url: ajax_object.ajaxurl,
			method: 'POST',
			data:{
				action: 'mytagactions',
				catid: ids
			},
			beforeSend: function() {
				$('#spinloader').addClass('loader_new');
			},
			success: function( data ) {
				$('#isTagsspan').html(data);
			},complete: function() {
				$('#spinloader').removeClass('loader_new');
			}
		});
		return false;
	});

	$(document).on('click','#plussign',function(){
		var droptext1 	= $("#isType option:selected").text();
		var droptext2 	= $("#isCat option:selected").text();
		var droptext3 	= $("#isTags option:selected").text();
		var dropval1 	= $("#isType option:selected").val();
		var dropval2 	= $("#isCat option:selected").val();
		var dropval3 	= $("#isTags option:selected").val();
		if(dropval1!='' && dropval2!='' && dropval3!=''){
			if($('#tagListtableid tr').length=='1'){
				$('#tagListtableid').removeClass('hideTable');
			}
			$('#tagListtableid').append('<tr><td>'+droptext1+'</td><td>'+droptext2+'</td><td>'+droptext3+'</td><td><input type="hidden" name="getAlltags[]" value="'+dropval3+'"><input type="hidden" name="getAllopt[]" value="'+dropval1+'"><a href="javascript:void(0);" class="button removeRows">Delete</td></tr>');
		}
		return false;
	});

	$('#tagListtableid').on('click', '.removeRows', function(){
		if($('#tagListtableid tr').length=='2'){
			$('#tagListtableid').addClass('hideTable');
		}
		$(this).closest ('tr').remove ();
	});

	$(document).on('click', '.deleterow', function(){
		var ids = $(this).data('id');
		var r = confirm("Are you sure delete this record?");
		if (r == true) {
			$.ajax({
				url: ajax_object.ajaxurl,
				method: 'POST',
				data:{
					action: 'mytagdeleteactions',
					id: ids
				},
				success: function( data ) {
					var url = window.location.href;    
					if (url.indexOf('?') > -1){
						url += '&del='+data;
					}else{
						url += '?del='+data;
					}
					window.location.href = url;
				}
			});
		}
		return false;
	});


});