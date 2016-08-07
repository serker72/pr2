 

$("#put_cost").bind("click", function(){
	
	if($("input[type=checkbox][id^=to_ship_]:checked").length==0){
		alert("�������� ������� ������������� ����������� ��� ������� ������!");
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
	 
	//����������� �����
	
	
	//����������� �����, ������� ����...
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
	rc=window.prompt("������� ����� ��:", value);
	return rc;	
}


//�������� ������������ ���������
function IsCorrectPriceF(sum){
	res=true;
	
	//�������� ����
	//sum=$("#positions_cost").html();	
	sum=sum.replace("\,","\.");
	if((sum.length==0)||isNaN(sum)||(parseFloat(sum)<=0)){
		//$("#positions_cost").addClass("wrong");
		alert("����������� ��������� ����� ��!");
		//$("#positions_cost").focus();
		res=res&&false;	
	}else{
		//$("#positions_cost").removeClass("wrong");
	}
	
	
	
	return res;	
}

//���������� ������ �� �����
function FindSkBySum(hash){
	
	res=true;
	
	var neat_sum=0; var sum_with_skidka=$("#check_new_total_"+hash).val().replace(/\,/,'.');
	
	//������ ���� �� ������������
	
	 
	neat_sum=parseFloat($("#new_quantity_"+hash).val().replace(/\,/,'.'))*parseFloat($("#new_price_"+hash).val().replace(/\,/,'.'));
	

	//alert(neat_sum);
	//�������� ��������� �� ��� ������
	
	//������ ������		
	
	delta=neat_sum-sum_with_skidka;
	
	
	
	skidka_percent=0;
	
	
	try{
		//������� ��� �� ��������� ����
		var pnr=0;
		
		 
		skidka_percent=roundPlus(100*delta/(neat_sum-pnr),2);
	}catch(e){
		alert("������ ��� ������� ������!");	
	}
	
	
	/*����� ��������� ����...
	����� - ��������� ���� �������� �������*/
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
	 //���� ��� ���� "752" � ������ ��������� ����. ���������� - �� � ���� "������ ���-��"
	 %{if !$can_override_ruk_discount}%
	 max_sk=$("#card_dl_value_1").val();	
	 max_sk=max_sk.replace("\,","\.");
	if(parseFloat(skidka_percent)>parseFloat(max_sk)){
		 
		field=$("#discount_text_2_"+hash);
		//$("#new_pl_discount_id_"+hash).val(2);
		kind_id=2;
		
		empty_field=$("#discount_text_1_"+hash);
		
		alert("��������! ��������� ���� ������ "+skidka_percent+"% ��������� ������������ ������ "+max_sk+"%.\n��������� ��� ������ ����� ����� ��������� ����������: %{strip}%
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


//����� ������
function card_discount_check(hash){
	res=true;
	
	
	local_res=true;
	
	sk=$("#card_discount_"+hash).val();	
	sk=sk.replace("\,","\.");
	
	 
	if((sk=="")||isNaN(sk)||(parseFloat(sk)<0)){
		res=res&&false;
		local_res=local_res&&false;	
		$("#card_discount_"+hash).focus();
		alert("����������� ��������� ���� "+$("label[for=card_discount_"+hash+"]").html()+"!");
		
		$("#card_discount_"+hash).addClass("wrong");
		
	}else $("#card_discount_"+hash).removeClass("wrong");
	
	
	//���� ������� ��������: �� �� ����� 99.99 ���������
	if(local_res){
	  if(!isNaN(sk)&&(parseFloat(sk)>99.99)){		
	  		res=res&&false;
			local_res=local_res&&false;	
			$("#card_discount_"+hash).focus();
			alert("����������� ��������� ���� "+$("label[for=card_discount_"+hash+"]").html()+"!");
			
			$("#card_discount_"+hash).addClass("wrong");
			
	  }else $("#card_discount_"+hash).removeClass("wrong");
	
	}
	
	
	 
	if(res) res=res&&IsCorrectBounds(hash);
	
	
	
	return res;
}


//�������� ������������ ������
//�������� ����-�� ������ (�����) Id - ��� �������

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
		alert("����������� ��������� �������� ���� %{$dis_in_card[discssec].name}%!");
		
		$("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").addClass("wrong");
		
	}else $("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").removeClass("wrong");
	
	
	//���� ������� ��������: �� �� ����� 99.99 ���������
	if(local_res){
	  if(!isNaN(sk)&&(parseFloat(sk)>99.99)){		
			res=res&&false;
			local_res=local_res&&false;	
			$("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").focus();
			alert("����������� ��������� �������� ���� %{$dis_in_card[discssec].name}%!");
			
			$("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").addClass("wrong");
			
	  }else $("#discount_text_"+"%{$dis_in_card[discssec].id}%"+"_"+id+"").removeClass("wrong");
	
	}
	
	 
	
	%{/section}%
	
	return res;
}
		

//�������� �� ������������������ ������ (������ �� �.�. ������ ������������)
function IsCorrectBounds(id){
	var res=true;
	
	%{section name=discssec loop=$dis_in_card}%
	/*can_override_manager_discount*/
	//���� ������ �� ��������, ���� ������ ������� ���������� � ���������� ���� ������ ���-��
	
	
	
	%{if  !($dis_in_card[discssec].id==2 and !$can_ruk_discount)}%
	// alert('%{$dis_in_card[discssec].id}% ');
	%{if  ($dis_in_card[discssec].id==1 and !$can_override_ruk_discount) 
	or ($dis_in_card[discssec].id==2 and !$can_override_ruk_discount) }%
	var local_res=true;
			
			 
	//��������� ��� ��������� ���������� � ��� ���� ������� ��������� �������� ������������
 
			 
		 
				
			
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
				
				 
				
				//���� ������ ��������, ������ ���� �� ������� 	���������� ������ ���������� � ���� ������ ���-��
				//alert('%{$dis_in_card[discssec].id}% ' + parseFloat(sk)+' '+parseFloat(max_sk));
				
				if(parseFloat(sk)>parseFloat(max_sk)){
					
				
						
						res=res&&false;
						alert("��������� ������ ��������� ������������ ������ "+max_sk_descr+"!");
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
	//��� ���� ������� - ��������� �������� ����-�� ������
	var res=true;
	
	$("input[id^=new_hash_]").each(function(index, element) {
        hash=$(element).val();
		
		if(res) res=res&&IsCorrectSk(hash);
		if(res) res=res&&IsCorrectBounds(hash);
		
		
    });
	
	if(res){
		//��������� ���� discount_more_than_max - ����� ���������, �� �� ����� ���������
		//��� ����� ����� 1 � ��� ������, ���� (��� �������)
		 	
		//��� - ������ ������������
		//��� ���� �� ������ ������������
		
		
		%{if !$can_ruk_discount}%
		var do_stop=false;
		$("input[id^=new_hash_]").each(function(index, element) {
	        hash=$(element).val();
			
			if(($("#new_pl_discount_id_"+hash).val()==2)&&($("#discount_text_2_"+hash).val()  >$("#card_dl_value_1").val())){
			//���� ��� ������ ������������, � ��� ��������� ����. ������ ��������� - ����.
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
		// ... �������� ���
		//�� ������ ���� 
		//new_price_%{$pospos[pospossec].hash}%
		//�������� ����
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
		
		//������� ������
		$.each($("input[id^=new_hash_]"),function(k,v){
			hash=$(v).val();
			$("#new_pl_discount_id_"+hash).val(sk_id);
			if($("#new_is_install_"+hash).val()==0) {
				
				$("#new_pl_discount_value_"+hash).val(skidka);
			}else $("#new_pl_discount_value_"+hash).val('0.00');
		});
		
		
		//� ������� ������ �������
		%{foreach from=$dis_in_card item=discs}%
		if("%{$discs.id}%"==sk_id){
			$("span[id^=discount_text_%{$discs.id}%]").html(skidka+" %");
		}else{
			$("span[id^=discount_text_%{$discs.id}%]").html('0.00');
		}
		%{/foreach}%
		
		//�������� ��� � �����
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
		
		//������� ��������� ������� ������� � ����� ����� ��
		
		//������� ������� ���������, ������ ���� ���� �����.
		//����� - �� ���������.
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


