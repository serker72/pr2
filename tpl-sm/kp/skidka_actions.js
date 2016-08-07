/*$("#positions_cost").bind("change", function(){
	res=true;
	
	res=res&&IsCorrectPriceF();
	
	if(res){
		FindSkBySum();	
	}
});*/ 

$("#put_cost").bind("click", function(){
	 
	res1=true;
	
	while(res1){
		res1=doInput( $("#positions_cost").html());
		
		if(!res1) break;
		
		rc=IsCorrectPriceF(res1);
		if(!rc) {
			res1=true;
			continue;
		}else{
			//alert(res1);
			$("#positions_cost").html(res1);
			FindSkBySum();
			res1=false;
		}
	}
});

function doInput(value){
	rc=window.prompt("Введите сумму КП:", $("#positions_cost").html());
	return rc;	
}


//проверка корректности стоимости
function IsCorrectPriceF(sum){
	res=true;
	
	//проверим цену
	//sum=$("#positions_cost").html();	
	sum=sum.replace("\,","\.");
	if((sum.length==0)||isNaN(sum)||(parseFloat(sum)<=0)){
		//$("#positions_cost").addClass("wrong");
		alert("Некорректно заполнена сумма КП!");
		//$("#positions_cost").focus();
		res=res&&false;	
	}else{
		//$("#positions_cost").removeClass("wrong");
	}
	
	
	
	return res;	
}

//нахождение скидке по сумме
function FindSkBySum(){
	
	res=true;
	
	var neat_sum=0; var sum_with_skidka=$("#positions_cost").html().replace(/\,/,'.');
	
	//найдем цену по оборудованию
	
	
	//прибавим опции 
	$.each($("input[id^=new_hash_]"),function(k,v){
			hash=$(v).val();
			/*$("#new_pl_discount_id_"+hash).val(sk_id);
			if($("#new_is_install_"+hash).val()==0) {
				
				$("#new_pl_discount_value_"+hash).val(skidka);
			}else $("#new_pl_discount_value_"+hash).val('0.00');*/
			
			neat_sum=neat_sum+parseFloat($("#new_quantity_"+hash).val().replace(/\,/,'.'))*parseFloat($("#new_price_"+hash).val().replace(/\,/,'.'));
	});
	
	

	//alert(neat_sum);
	//получена стоимость КП без скидки
	
	//найдем скидку		
	
	delta=neat_sum-sum_with_skidka;
	
	
	
	skidka_percent=0;
	
	
	try{
		//вычесть ПНР из возможных сумм
		var pnr=0;
		
		$.each($("input[id^=new_hash_]"),function(k,v){
			hash=$(v).val();
			if(($("#new_is_install_"+hash).val()==1) ){
			
				pnr=parseFloat($("#new_quantity_"+hash).val().replace(/\,/,'.'))*parseFloat($("#new_price_"+hash).val().replace(/\,/,'.')) ;
			}
			
		});
		
	   
		
		skidka_percent=roundPlus(100*delta/(neat_sum-pnr),2);
	}catch(e){
		alert("Ошибка при расчете скидки!");	
	}
	
	
	/*найти доступное поле...
	далее - проверять весь комплекс условий*/
	if($("#card_discount_1").prop("disabled")) field=$("#card_discount_2");
	else if($("#card_discount_2").prop("disabled")) field=$("#card_discount_1"); 
	
	
	//alert(skidka_percent);
	 //если нет прав "752" и скидка превышает макс. допустимую - то в поле "скидка рук-ля"
	 %{if !$can_override_ruk_discount}%
	 max_sk=$("#card_dl_value_1").val();	
	 max_sk=max_sk.replace("\,","\.");
	if(parseFloat(skidka_percent)>parseFloat(max_sk)){
		 
		field=$("#card_discount_2");
		
		alert("Внимание! Введенная Вами скидка "+skidka_percent+"% превышает максимальную скидку "+max_sk+"%.\nУтвердить эту скидку имеют право следующие сотрудники: %{strip}%
		%{foreach name=us from=$users_can_override_ruk_discount item=user}%
		%{$user.name_s}% (%{$user.login}%)%{if !$smarty.foreach.us.last}%, %{/if}%
		
		%{/foreach}%
		%{/strip}%!");
	}
	 %{/if}%
	 
	 /*
	$("#card_discount_1").val(skidka_percent);
	
	$("#card_discount_1").trigger("change");
	 */
	 $(field).val(skidka_percent).trigger("change");
	
	return res;	
	
}


//смена скидок
function card_discount_check(hash){
	res=true;
	
	
	local_res=true;
	
	sk=$("#card_discount_"+hash).val();	
	sk=sk.replace("\,","\.");
	
	 
	if((sk=="")||isNaN(sk)||(parseFloat(sk)<0)){
		res=res&&false;
		local_res=local_res&&false;	
		$("#card_discount_"+hash).focus();
		alert("Некорректно заполнено поле "+$("label[for=card_discount_"+hash+"]").html()+"!");
		
		$("#card_discount_"+hash).addClass("wrong");
		
	}else $("#card_discount_"+hash).removeClass("wrong");
	
	
	//если выбраны ПРОЦЕНТЫ: то не более 99.99 процентов
	if(local_res){
	  if(!isNaN(sk)&&(parseFloat(sk)>99.99)){		
	  		res=res&&false;
			local_res=local_res&&false;	
			$("#card_discount_"+hash).focus();
			alert("Некорректно заполнено поле "+$("label[for=card_discount_"+hash+"]").html()+"!");
			
			$("#card_discount_"+hash).addClass("wrong");
			
	  }else $("#card_discount_"+hash).removeClass("wrong");
	
	}
	
	
	 
	if(res) res=res&&IsCorrectBounds(hash);
	
	 
	
	return res;
}



//проверка на взаимокорректность скидок (скидка не м.б. больше максимальной)
function IsCorrectBounds(hash){
	res=true;
	
	 
	
	local_res=true;
	
	sk=$("#card_discount_"+hash).val();	
	sk=sk.replace("\,","\.");
	sk=parseFloat(sk);
	
	
	max_sk=$("#card_dl_value_"+hash).val();	
	max_sk=max_sk.replace("\,","\.");
	max_sk=parseFloat(max_sk);
	
//	alert(hash);
//	alert(parseInt("%{$can_override_ruk_discount}%"));
	can_override_ruk_discount=parseInt("%{$can_override_ruk_discount}%");
	can_override_manager_discount=parseInt("%{$can_override_manager_discount}%");
	
	%{if !$can_override_ruk_discount}%
		 if(hash==1){
			// alert(sk+' vs '+max_sk);
			if(sk>max_sk){
				
				//если это скидка менеджера, и нет прав на скидку руководителя, и введено значение больше макс. скикди - перебросить ее в скидку руководителя
				%{if    !$can_ruk_discount  }%
					$("#card_discount_2").val($("#card_discount_1").val());//.trigger("change");
					UpdateSk(2);
					return false; 
					
				%{else}% 
				
				
					 res=res&&false;
					alert("Введенная скидка превышает максимальную скидку "+$("#card_dl_value_"+hash).val()+"%!");
					$("#card_discount_"+hash).addClass("wrong"); 
				
				%{/if}%		 
				
			}else{
				$("#card_discount_"+hash).removeClass("wrong");
				
				//$("#discount_more_than_max").val(0);
				
			}
		 }
		 if(hash==2){
			 if(sk>max_sk){
				 $("#discount_more_than_max").val(1);
			  }else{
				 $("#discount_more_than_max").val(0);
				
			}
		 }
		 
	%{/if}%
	
	 
	
	 
	
	
	if(res){
		$("#card_discount_"+hash).removeClass("wrong");
	}
	 
	
	return res;	
}



function UpdateSk(sk_id){
	//return card_discount_check("%{$discs.id}%");
	
	res=card_discount_check(sk_id);
	if(res) $.each($("input[id^=card_discount_]"), function(k,v){
			if($(v).attr("id")!=="card_discount_"+sk_id){
				 $(v).val("0.00");
				 $(v).removeClass("wrong");
			}
	});
	
	var skidka=$("#card_discount_"+sk_id).val();
	
	if(res){
		// ... пересчет цен
		//на основе цены 
		//new_price_%{$pospos[pospossec].hash}%
		//поменять поля
		//
		//new_pl_discount_id_%{$pospos[pospossec].hash}%
		//new_pl_discount_value_%{$pospos[pospossec].hash}%
		
		//discount_text_%{$discs.id}%_%{$pospos[pospossec].hash}% - html
		//new_price_f_%{$pospos[pospossec].hash}% - val
		//check_new_price_pm_ -val
		//new_price_pm_%{$pospos[pospossec].hash}% - html
		//new_total_%{$pospos[pospossec].hash}%  - html
		//check_new_total_%{$pospos[pospossec].hash}% - val
		//positions_cost - val
		
		//обновим скидку
		$.each($("input[id^=new_hash_]"),function(k,v){
			hash=$(v).val();
			$("#new_pl_discount_id_"+hash).val(sk_id);
			if($("#new_is_install_"+hash).val()==0) {
				
				$("#new_pl_discount_value_"+hash).val(skidka);
			}else $("#new_pl_discount_value_"+hash).val('0.00');
		});
		
		
		//в видимых графах таблицы
		%{foreach from=$dis_in_card item=discs}%
		if("%{$discs.id}%"==sk_id){
			$("span[id^=discount_text_%{$discs.id}%]").html(skidka+" %");
		}else{
			$("span[id^=discount_text_%{$discs.id}%]").html('0.00');
		}
		%{/foreach}%
		
		//пересчет цен и итого
		skidka=skidka.replace(/\,/, '.');
		skidka=parseFloat(skidka);
		
		var itogo=0;
		$.each($("input[id^=new_hash_]"),function(k,v){
			hash=$(v).val();
			price=parseFloat($("#new_price_"+hash).val());
			quantity=parseFloat($("#new_quantity_"+hash).val());
			
			if($("#new_is_install_"+hash).val()==0) price_f=price-price*skidka/100;
			else price_f=price;
			
			$("#new_price_f_"+hash).val(roundPlus(price_f,%{$digits|default:"0"}%));
			
			 $("#new_price_pm_"+hash).html(roundPlus(price_f,%{$digits|default:"0"}%));
			
			 $("#check_new_price_pm_"+hash).val(roundPlus(price_f,%{$digits|default:"0"}%));
			
			cost=price_f*quantity;
			
			 $("#new_total_"+hash).html(roundPlus(cost,%{$digits|default:"0"}%));
			
			 $("#check_new_total_"+hash).val(roundPlus(cost,%{$digits|default:"0"}%));
			
			itogo+=cost;
			
		});
		
		//обновим стоимость главной позиции и общую сумму КП
		
		//главную позицию обновлять, только если есть опции.
		//иначе - не обновлять.
		var count_of_options=0;
		$.each($("input[id^=new_parent_id_]"),function(k,v){
			 
			if($(v).val()!=0) count_of_options++;
		});
		
		if(count_of_options>0) $.each($("input[id^=new_hash_]"),function(k,v){
			hash=$(v).val();
			if($("#new_parent_id_"+hash).val()==0) {
				
				 $("#new_total_"+hash).html(roundPlus(itogo,%{$digits|default:"0"}%));
				
				 $("#check_new_total_"+hash).val(roundPlus(itogo,%{$digits|default:"0"}%));
			}
		});
		
		 $("#positions_cost").html(roundPlus(itogo,%{$digits|default:"0"}%));
		
	}
}


