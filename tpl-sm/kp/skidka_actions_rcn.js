 

$("#put_cost").bind("click", function(){
	
	if($("input[type=checkbox][id^=to_ship_]:checked").length==0){
		alert("Выберите позиции коммерческого предложения для подбора скидки!");
		return;	
	}
	
	
	$("input[type=checkbox][id^=to_ship_]:checked").each(function(index, element) {
        res1=true;
		
		hash=$(this).attr("id").replace(/^to_ship_/, '');
		
		
		while(res1){
			res1=doInput( $("#check_new_total_"+hash).val());
			
			if(!res1) break;
			
			rc=IsCorrectPriceF(res1);
			if(!rc) {
				res1=true;
				continue;
			}else{
				//alert(res1);
				$("#check_new_total_"+hash).val(res1);
				$("#new_total_"+hash).html(res1);
				
				FindSkBySum(hash);
				res1=false;
			}
		}
    });
	 
	//пересчитать итого
	
	
	//пересчитать итого, сменить цены...
	var cost=0;
	$.each($("#positions table tbody tr td input[type=hidden][id^='new_hash_']"), function(key, value){
					hash1=$(value).val();
					sum=parseFloat($("#new_quantity_"+hash1).val())*parseFloat($("#new_price_"+hash1).val());
					sum=sum-sum*parseFloat($("#new_pl_discount_value_"+hash1).val())/100;
					
					price_f=parseFloat($("#new_price_"+hash1).val()) - parseFloat($("#new_price_"+hash1).val())*parseFloat($("#new_pl_discount_value_"+hash1).val())/100;
					
					$("#new_price_f_"+hash1).val(Math.round(price_f));
					$("#new_price_pm_"+hash1).html(Math.round(price_f));
					$("#check_new_price_pm_"+hash1).val(Math.round(price_f));
					
					cost+=sum;
					
	});
					
	
	
	$("#positions_cost").html(Math.round(cost));
	
	
});

function doInput(value){
	rc=window.prompt("Введите сумму КП:", value);
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
function FindSkBySum(hash){
	
	res=true;
	
	var neat_sum=0; var sum_with_skidka=$("#check_new_total_"+hash).val().replace(/\,/,'.');
	
	//найдем цену по оборудованию
	
	 
	neat_sum=parseFloat($("#new_quantity_"+hash).val().replace(/\,/,'.'))*parseFloat($("#new_price_"+hash).val().replace(/\,/,'.'));
	

	//alert(neat_sum);
	//получена стоимость КП без скидки
	
	//найдем скидку		
	
	delta=neat_sum-sum_with_skidka;
	
	
	
	skidka_percent=0;
	
	
	try{
		//вычесть ПНР из возможных сумм
		var pnr=0;
		
		 
		skidka_percent=roundPlus(100*delta/(neat_sum-pnr),2);
	}catch(e){
		alert("Ошибка при расчете скидки!");	
	}
	
	
	/*найти доступное поле...
	далее - проверять весь комплекс условий*/
	if($("#card_discount_1").prop("disabled")) {
		field=$("#discount_text_2_"+hash);
		//$("#new_pl_discount_id_"+hash).val(2);
		kind_id=2;
		
		empty_field=$("#discount_text_1_"+hash);
	}else if($("#card_discount_2").prop("disabled")){
		 field=$("#discount_text_1_"+hash); 
		  
		 kind_id=1;
		
		empty_field=$("#discount_text_2_"+hash);
	}
	
	 
	
	//alert(skidka_percent);
	 //если нет прав "752" и скидка превышает макс. допустимую - то в поле "скидка рук-ля"
	 %{if !$can_override_ruk_discount}%
	 max_sk=$("#card_dl_value_1").val();	
	 max_sk=max_sk.replace("\,","\.");
	if(parseFloat(skidka_percent)>parseFloat(max_sk)){
		 
		field=$("#discount_text_2_"+hash);
		//$("#new_pl_discount_id_"+hash).val(2);
		kind_id=2;
		
		empty_field=$("#discount_text_1_"+hash);
		
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
	 $(field).val(skidka_percent);
	 $("#new_pl_discount_value_"+hash).val(skidka_percent);
	 $(empty_field).val(0);
	 $("#new_pl_discount_id_"+hash).val(kind_id);
	 
	 //
	 
	
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


//проверка корректности скидки
//проверка корр-ти скидки (любой) Id - хэш позиции

function IsCorrectSk(id){
	res=true;

	
	%{section name=discssec loop=$dis_in_card}%
	
	local_res=true;
	sk=$("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").val();	
	sk=sk.replace("\,","\.");
	if((sk=="")||isNaN(sk)||(parseFloat(sk)<0)){
		res=res&&false;
		local_res=local_res&&false;	
		$("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").focus();
		alert("Некорректно заполнено значение поля %{$dis_in_card[discssec].name}%!");
		
		$("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").addClass("wrong");
		
	}else $("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").removeClass("wrong");
	
	
	//если выбраны ПРОЦЕНТЫ: то не более 99.99 процентов
	if(local_res){
	  if(!isNaN(sk)&&(parseFloat(sk)>99.99)){		
			res=res&&false;
			local_res=local_res&&false;	
			$("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").focus();
			alert("Некорректно заполнено значение поля %{$dis_in_card[discssec].name}%!");
			
			$("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").addClass("wrong");
			
	  }else $("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").removeClass("wrong");
	
	}
	
	 
	
	%{/section}%
	
	return res;
}
		

//проверка на взаимокорректность скидок (скидка не м.б. больше максимальной)
function IsCorrectBounds(id){
	var res=true;
	
	%{section name=discssec loop=$dis_in_card}%
	/*can_override_manager_discount*/
	//блок должен НЕ работать, если скидка введена программно в неактивное поле Скидка рук-ля
	
	
	
	%{if  !($dis_in_card[discssec].id==2 and !$can_ruk_discount)}%
	// alert('%{$dis_in_card[discssec].id}% ');
	%{if  ($dis_in_card[discssec].id==1 and !$can_override_ruk_discount) 
	or ($dis_in_card[discssec].id==2 and !$can_override_ruk_discount) }%
	var local_res=true;
			
			 
	//перебрать все ненулевые количества и для этих позиций выполнить проверки корректности
 
			 
		 
				
			
				sum=$("#new_price_"+id+"").val();	
				sum=sum.replace("\,","\.");
				
				sk=$("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").val();	
				sk=sk.replace("\,","\.");
				
				max_sk=$("#card_dl_value_"+"%{$dis_in_card[discssec].id}%").val();	
				max_sk=max_sk.replace("\,","\.");
				
				
				sk_in_rub=0;
				max_sk_in_rub=0;
				
			 
					sk_in_rub=roundPlus(parseFloat(sum)*parseFloat(sk)/100,0);	
			 
				
				
				max_sk_descr='';
				if(max_sk!=""){
					 
						max_sk_in_rub=roundPlus(parseFloat(sum)*parseFloat(max_sk)/100,0);	
						max_sk_descr=max_sk+'% ';
					 
				}else max_sk_in_rub=sum;
				
				 
				
				//блок должен работать, только если НЕ введена 	программно скидка менеджером в поле скидка рук-ля
				//alert('%{$dis_in_card[discssec].id}% ' + parseFloat(sk)+' '+parseFloat(max_sk));
				
				if(parseFloat(sk)>parseFloat(max_sk)){
					
				
						
						res=res&&false;
						alert("Введенная скидка превышает максимальную скидку "+max_sk_descr+"!");
						$("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").addClass("wrong");
						 
					 
				}else{
					$("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").removeClass("wrong");
					 
				}
			
			   
			   
			   
			   
			   
 
	%{/if}%
	%{/if}%
	
	
	
	
	%{/section}%
	
	return res;	
}		
		
function CheckSks(){
	//для всех позиций - выполнить проверку корр-ти скидок
	var res=true;
	
	$("input[id^=new_hash_]").each(function(index, element) {
        hash=$(element).val();
		
		if(res) res=res&&IsCorrectSk(hash);
		if(res) res=res&&IsCorrectBounds(hash);
		
		
    });
	
	if(res){
		//заполнить поле discount_more_than_max - может сохранить, но не может утвердить
		//оно будет равно 1 в том случае, если (все условия)
		 	
		//это - скидка руководителя
		//нет прав на скидку руководителя
		
		
		%{if !$can_ruk_discount}%
		var do_stop=false;
		$("input[id^=new_hash_]").each(function(index, element) {
	        hash=$(element).val();
			
			if(($("#new_pl_discount_id_"+hash).val()==2)&&($("#discount_text_2_"+hash).val()  >$("#card_dl_value_1").val())){
			//если это скидка руководителя, и она превышает макс. скидку менеджера - стоп.
				//alert ($("#discount_text_2_"+hash).val()  + ' '+$("#card_dl_value_1").val() );
				 
				do_stop=do_stop||true;
			}
		});
		
		if(do_stop){
			$("#discount_more_than_max").val(1)	;
		}else{
			$("#discount_more_than_max").val(0)	;
		}
		%{else}%
		$("#discount_more_than_max").val(0)	;
		%{/if}%	
	}
	
	
	return res;
}

 



function UpdateSk(sk_id){
	//return card_discount_check("%{$discs.id}%");
	
	res=card_discount_check(sk_id);
	 $.each($("input[id^=card_discount_]"), function(k,v){
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


