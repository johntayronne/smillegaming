jQuery(document).ready(function($){
	
	var ajaxurl = '/wp-admin/admin-ajax.php',
		wpggma_g2async = false;
	
	$('textarea[name=wpggma_tool_parse_textarea]').numberedtextarea();
	
	function currentDate(){
		function addZero(i) {
			if (i < 10) {
				i = "0" + i;
			}
			return i;
		}
		var date = new Date();
		hour 		= addZero(date.getHours());
		min  		= addZero(date.getMinutes());
		sec  		= addZero(date.getSeconds());
		return hour + ':' + min + ':' + sec;

	}
	
	// Admin
	$('input[name=wpggma_RewriteBaseSlug], input[name=wpggma_RewriteGameSlug]').bind('input', function(){
		$(this).val($(this).val().replace(/[^a-z0-9-]/gi, ''));
	});
	
	$("input[name=wpggma_RewriteBaseSlug]").keyup(function(event) {
		$(".wpggma_RewriteBaseSlug_mirror").text($(this).val());
	});
	
	$("input[name=wpggma_RewriteGameSlug]").keyup(function(event) {
		$(".wpggma_RewriteGameSlug_mirror").text($(this).val());
	});
	
	$('select[name=wpggma_RewriteGameName]').change(function(){
		if ($(this).find(":selected").val() == 'id'){
			$(".wpggma_RewriteGameName_mirror").text(2450);
		} else {
			$(".wpggma_RewriteGameName_mirror").text('game-name');
		}
	});
	
	// Ajax
	$("a.wpggma_ActivateLink").click(function(){
		
		var data = {action:'wpggma_activate'};
		jQuery.post(ajaxurl, data).done(function(response){
			if(response != 0){
				$('.wpggma_ActivateLink').removeClass('button-primary');
				$('.wpggma_ActivateLink').addClass('button');
				$('body .hide').removeClass('hide');
				$('.wpggma_ActivateLink_text').remove();
				$('input[type=text]:disabled').attr('disabled', false);
				window.location.reload();
			}
		});
	});
	
	// Delete
	$(".wpggma_links").on('click', 'a.wpggma_DeleteLink', function(event){
		event.preventDefault();
		var id = $(this).attr('data-id');
		var data = {
			action: 'wpggma_delete_link',
			data: id
		};
		jQuery.post(ajaxurl, data).done(function(response){
			if(response != 0){
				$('.wpggma_links tbody').find("tr[data-id=" + id + "]").fadeOut(700);
				setTimeout(function(){
					$(".wpggma_links tbody").find("tr[data-id=" + id + "]").remove();
				}, 700);
				
			}
		});
	});
	
	// ReSync.
	$(".wpggma_links").on('click', 'a.wpggma_ReSyncLink', function(event){
		event.preventDefault();
		
		var id = $(this).attr('data-id');
		$(".wpggma_links tbody").find("tr[data-id=" + id + "] .wpggma_ReSyncLink").hide();
		$(".wpggma_links tbody").find("tr[data-id=" + id + "] .wpggma_search_added").html('<img src="/wp-admin/images/spinner.gif" />');
		
		var data = {
			action: 'wpggma_resync_link',
			data: id
		};
		jQuery.post(ajaxurl, data).done(function(response){
			if(response != 0){
				$(".wpggma_links tbody").find("tr[data-id=" + id + "]").replaceWith(response);
			}
		});
	});
	
	// ReSync. All
	function wpggma_ReSyncAll(offset = 0){
		
		var id = $(".wpggma_links tbody tr:eq(" + offset + ")").first().attr('data-id');
		if(id != "undefined" && id != null && id != 0){
			$(".wpggma_links tbody").find("tr[data-id=" + id + "] .wpggma_ReSyncLink").hide();
			$(".wpggma_links tbody").find("tr[data-id=" + id + "] .wpggma_search_added").html('<img src="/wp-admin/images/spinner.gif" />');
			
			var data = {
				action: 'wpggma_resync_link',
				data: id
			};
			jQuery.post(ajaxurl, data).done(function(response){
				if(response != 0){
					$(".wpggma_links tbody").find("tr[data-id=" + id + "]").replaceWith(response);
					offset++;
					wpggma_ReSyncAll(offset);
				}
			});
		}
		
	}
	$(".wpggma_ReSyncAll").click(function(event){
		event.preventDefault();
		wpggma_ReSyncAll();
	});
	
	// Search
	$(".wpggma_search").submit(function(event){
		event.preventDefault();
		
		tb_show("Search G2A","#TB_inline?i=1&amp;width=50&amp;height=50&amp;inlineId=modal_window",null);
		$('.wpggma_search_results').html('');
		$('.wpggma_search_error').html('');
		
		if($('input[name=wpggma_search_link]').val() == '')
			$('input[name=wpggma_search_link]').val('*');
			
		var dataForm = $(this).serialize();
		
		if(dataForm.length > 0){
			
			$('.wpggma_search_results').html('<div style="height:100%; width:100%; position:relative; background:#f8f8f8;"><img src="/wp-admin/images/spinner.gif" style="position:absolute; top:50%; left:50%; margin-top:-10px; margin-left:-10px;" /></div>');
			
			var data = {action:'wpggma_search', data: dataForm};
			jQuery.post(ajaxurl, data).done(function(response){
				if(response != 0){
					$('.wpggma_search_results').html(response);
					$('.wpggma_search_error').html('');
					
					// Add Link
					$(".wpggma_add_link").submit(function(event) {
						event.preventDefault();
						var form = $(this);
						var dataForm = $(this).serialize();
						if(dataForm.length > 0){

							$('.wpggma_search_error').html('');
							
							var data = {
								action: 'wpggma_add_link',
								data: dataForm
							};
							
							jQuery.post(ajaxurl, data).done(function(response){
								if(response != 0){
									form.find('.wpggma_add_link_wrapper').html('<img src="/wp-admin/images/spinner.gif" />');
									
									$('.wpggma_links tbody tr').first().before(response);
									$('.wpggma_links tbody tr').first().hide();
									
									var data = {
										value: 	$('.wpggma_links tbody tr').first().find('.wpggma_link_ref').val(),
										redirect: $('.wpggma_links tbody tr').first().find('.wpggma_link_url').val()
									};
									$.ajax({
										url: 'https://www.g2a.com/goldmine/reflinks/create/',
										type: 'GET',
										data: data,
										dataType: 'jsonp',
										crossDomain: true
									});
									
									// Synchronize
									setTimeout(function(){
										var data = {
											action: 'wpggma_check_ref',
											data: $('.wpggma_links tbody tr').first().attr('data-id')
										};
										
										jQuery.post(ajaxurl, data).done(function(response){
											
											// Success
											if(response != 0){
												$('.wpggma_search_error').html('');
												$('.wpggma_links tbody tr').first().fadeIn(700);
												$('.wpggma_links_counter').html(parseInt($('.wpggma_links_counter').text()) + 1);
												//$('.wpggma_links tbody tr').first().find('.wpggma_search_added').html('<div class="text-success" style="margin-bottom:7px;">G2A Sync. Completed</div>');
												form.find('.wpggma_add_link_wrapper').html('<span class="text-success"><i class="dashicons dashicons-v-middle dashicons-yes"></i> Link Added!</span>');
												
												if($('.wpggma_links tbody tr').last().attr('data-id') == 0)
													$('.wpggma_links tbody tr').last().remove();
											
											// Fail
											}else{
												$('.attachments-browser').scrollTop(0);
												$('.wpggma_search_error').html('<div style="padding:10px; padding-bottom:3px;"><div class="notice notice-error" style="margin:0;"><p><strong>G2A Synchronization Failed!</strong></p><ul><li style="list-style:square inside;">Go to your <a href="https://www.g2a.com/goldmine/join/us/Z49U47G6T" target="_blank">G2A Goldmine Dashboard</a></li><li style="list-style:square inside;">Make sure you\'re logged in</li><li style="list-style:square inside;">Check your 100 links limit</li></ul></div></div>');
												$('.wpggma_links tbody tr').first().remove();
												form.find('.wpggma_add_link_wrapper').html('<button class="button"><i class="dashicons dashicons-v-middle dashicons-plus"></i> Add Link</button>');
												
											}

										});
									}, 4000);
									
								}
							});

							
						}
					});
				}
			});
			
		}
	});
	

	// Tool: Parse Logs
	$('.currentDate').html(currentDate()).removeClass('currentDate');
	$('.wpggma_tool_parse_logs').bind('contentchanged', function() {
		$('.currentDate').html(currentDate()).removeClass('currentDate');
		$(".wpggma_tool_parse_logs").scrollTop($(".wpggma_tool_parse_logs")[0].scrollHeight);
	});
	
	$('.wpggma_tool_parse_logs_clear').click(function(event){
		event.preventDefault();
		$('.wpggma_tool_parse_logs').html('<div>---------------------------------------</div><div>[<span class="currentDate"></span>] <em>Waiting for source code.</em></div><div>---------------------------------------</div>').trigger('contentchanged');
	});
	
	// Tool: Parse Submit
	$(".wpggma_tool_parse").submit(function(event){
		event.preventDefault();
		
		if($('textarea[name=wpggma_tool_parse_textarea]').val() == ''){
			$('.wpggma_tool_parse_logs div').last().after('<div>[<span class="currentDate"></span>] Initializing...</div>');
			$('.wpggma_tool_parse_logs div').last().after('<div>[<span class="currentDate"></span>] <span class="text-danger">No data found.</span></div>');
			$('.wpggma_tool_parse_logs div').last().after('<div>---------------------------------------</div>').trigger('contentchanged');
			return;
		}
			
		var dataForm = $(this).serialize();
		
		if(dataForm.length > 0){
			
			function wpggma_tool_parse_link(dataForm, offset, total){
				var data = {
					action: 'wpggma_tool_parse',
					data: 	dataForm,
					offset: offset,
					total: 	total
				};
				jQuery.post(ajaxurl, data).done(function(response){
					if(response != 0){
						
						$('.wpggma_tool_parse_logs div').last().after(response).trigger('contentchanged');
						offset++;
						if(total > offset){
							wpggma_tool_parse_link(dataForm, offset, total);
							
						}else{
							$('.wpggma_tool_parse_logs div').last().after('<div>[<span class="currentDate"></span>] Finished Jobs.</div>');
							$('.wpggma_tool_parse_logs div').last().after('<div>---------------------------------------</div>').trigger('contentchanged');
							$('textarea[name=wpggma_tool_parse_textarea]').attr('disabled', false);
						}
					}
				});
			}
			
			$('.wpggma_tool_parse_logs div').last().after('<div>[<span class="currentDate"></span>] Initializing...</div>').trigger('contentchanged');
			$('textarea[name=wpggma_tool_parse_textarea]').attr('disabled', true);
			
			var data = {
				action:'wpggma_tool_parse',
				data: dataForm
			};
			jQuery.post(ajaxurl, data).done(function(response){
				if(response != 0){
					
					var offset = 0;
					var total = $(response).filter('.wpggma_tool_parse_total').val();
					
					if(total > 0){
						$('.wpggma_tool_parse_logs div').last().after(response);
						wpggma_tool_parse_link(dataForm, offset, total);
						
					}else{
						$('.wpggma_tool_parse_logs div').last().after(response);
						$('.wpggma_tool_parse_logs div').last().after('<div>---------------------------------------</div>').trigger('contentchanged');
						$('textarea[name=wpggma_tool_parse_textarea]').attr('disabled', false);
						
					}
				}
			});
		}
	});

});