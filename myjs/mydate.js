function mydate(thediv, date_name, opt){
	var box = $(thediv);
	var sel = $('<select></select>');
	box.append(sel.clone().addClass('year')).append(sel.clone().addClass('month')).append(sel.addClass('day'));
	$('<input type="hidden"/>').attr('name', date_name).appendTo(box);
	
	var year = box.children('select.year:first');
	var month = box.children('select.month:first');
	var day = box.children('select.day:first');
	var the_date = box.children('input:first');
	
	var opt_tpl = $('<option></option>');
	for(var i=2012; i>=1900; i--){
		opt_tpl.clone().val(i).text(i).appendTo(year);
	}
	
	var str_i='';
	for(var i=1; i<=12; i++){
		if(i<10){
			str_i = '0'+i;
		}else{
			str_i = i;
		}
		opt_tpl.clone().val(str_i).text(str_i).appendTo(month);
	}
	
	if(opt && opt.noday==true){
		day.remove();
	}else{
	
		box.children('select.year, select.month').change(function(){
			var year_val = year.val();
			var month_val = month.val();
			
			year_val = parseInt(year_val,10);
			month_val = parseInt(month_val,10);

			var endday = 0;
			if(month_val==2){
				if((year_val % 4 == 0 && year_val % 100 != 0) || year_val % 400 == 0){
					endday = 29;
				}else{
					endday = 28;
				}
			}else{
				endday = [31,28,31,30,31,30,31,31,30,31,30,31][month_val-1];
			}
			
			lastday = day.children(':last').val();
			
			if(!lastday){ lastday=0; }
			
			lastday = parseInt(lastday,10);
			endday = parseInt(endday,10);
			
			if(lastday==endday){return;}
			else if(lastday>endday){
				for(var i=endday+1; i<=lastday; i++){
				
					if(i<10){
						str_i = '0'+i;
					}else{
						str_i = i;
					}
					
					day.children('[value='+str_i+']').remove();
				}
			}else{
				for(var i=lastday+1; i<=endday; i++){
				
					if(i<10){
						str_i = '0'+i;
					}else{
						str_i = i;
					}
					
					opt_tpl.clone().val(str_i).text(str_i).appendTo(day);
				}
			}
			
		});
	}
	
	box.children('select').change(function(){
		var date_val = '';
		var fuhao = '';
		box.children('select').each(function(){
			date_val += fuhao + $(this).val();
			fuhao = '-';
		});
		the_date.val(date_val);
	}).css('margin-right', '5px');
	
	if(opt && opt.default_val){
		the_date.val(opt.default_val);
		dval = opt.default_val.split('-');
		year.attr('value', dval[0]);
		month.attr('value', dval[1]);
	}
	
	year.change();
	
	if(opt &&  !opt.noday){day.attr('value', dval[2]).change();}
	
}