<?
    // Найдём величину амортизационных отчислений (АО)
    $Ha = round(100 / $equipment_service_life, 4);
    if ($k_amortiz > 0) $Ha = round($Ha * $k_amortiz, 4);
    
    for($i=1;$i<=$equipment_service_life;$i++) {
        $u_amortiz[$i] = array($i,0,0,0,0);
        
        if ($i == 1) {
            $u_amortiz[$i][1] = round(($equipment_cost * $Ha) / 100, 3);
            $u_amortiz[$i][2] = $u_amortiz[$i][1];
            $u_amortiz[$i][3] = $equipment_cost - $u_amortiz[$i][2];
            $u_amortiz[$i][4] = round($u_amortiz[$i][1] / 12, 3);
        } elseif ($i == $equipment_service_life) {
            $u_amortiz[$i][1] = $u_amortiz[$i-1][3];
            $u_amortiz[$i][2] = $u_amortiz[$i-1][2] + $u_amortiz[$i][1];
            $u_amortiz[$i][3] = $equipment_cost - $u_amortiz[$i][2];
            $u_amortiz[$i][4] = round($u_amortiz[$i][1] / 12, 3);
        } else {
            $u_amortiz[$i][1] = round(($u_amortiz[$i-1][3] * $Ha) / 100, 3);
            $u_amortiz[$i][2] = $u_amortiz[$i-1][2] + $u_amortiz[$i][1];
            $u_amortiz[$i][3] = $equipment_cost - $u_amortiz[$i][2];
            $u_amortiz[$i][4] = round($u_amortiz[$i][1] / 12, 3);
        }
    }
    
    // Найдём плату за использованные кредитные ресурсы (ПЛ)
    if ($credit_rate > 0) {
        // Найдём коэффициент наращения ренты с  неоднократной выплатой и неоднократным начислением процентов в год по формуле: S = ( ( 1 + J / m ) n * m – 1 ) / J
        $S = round((pow(1 + $credit_rate/100/12, $contract_time * 12) - 1) / ($credit_rate/100), 7);
        
        // Найдём ежегодный платёж по формуле: Y = D * ( 1 + J / m ) n*m   / S
        if ($S > 0) {
            $Y = round(($equipment_cost * pow(1 + $credit_rate/100/12, $contract_time * 12)) / $S, 3);
            $Y_m = round($Y / 12, 3);
        }
    }
    
    // Найдём лизинговую премию за предоставленную услугу (ЛП)
    
    // Найдём среднюю остаточную стоимость имущества за каждый год
    for($i=1;$i<=$equipment_service_life;$i++) {
        $sy_cost[$i] = array($i,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
        
        if ($i == 1) { 
            $sy_cost[$i][ 1] = $equipment_cost;
        } else {
            $sy_cost[$i][ 1] = $sy_cost[$i-1][13];
        }
        
        $sy_cost[$i][ 2] = round($sy_cost[$i][1] -  1 * $u_amortiz[$i][4], 3);
        $sy_cost[$i][ 3] = round($sy_cost[$i][1] -  2 * $u_amortiz[$i][4], 3);
        $sy_cost[$i][ 4] = round($sy_cost[$i][1] -  3 * $u_amortiz[$i][4], 3);
        $sy_cost[$i][ 5] = round($sy_cost[$i][1] -  4 * $u_amortiz[$i][4], 3);
        $sy_cost[$i][ 6] = round($sy_cost[$i][1] -  5 * $u_amortiz[$i][4], 3);
        $sy_cost[$i][ 7] = round($sy_cost[$i][1] -  6 * $u_amortiz[$i][4], 3);
        $sy_cost[$i][ 8] = round($sy_cost[$i][1] -  7 * $u_amortiz[$i][4], 3);
        $sy_cost[$i][ 9] = round($sy_cost[$i][1] -  8 * $u_amortiz[$i][4], 3);
        $sy_cost[$i][10] = round($sy_cost[$i][1] -  9 * $u_amortiz[$i][4], 3);
        $sy_cost[$i][11] = round($sy_cost[$i][1] - 10 * $u_amortiz[$i][4], 3);
        $sy_cost[$i][12] = round($sy_cost[$i][1] - 11 * $u_amortiz[$i][4], 3);
        $sy_cost[$i][13] = round($sy_cost[$i][1] - 12 * $u_amortiz[$i][4], 3);

        $t_s = 0;
        for($j=1;$j<14;$j++) { $t_s = $t_s + $sy_cost[$i][$j]; }

        $sy_cost[$i][14] = round($t_s/13, 3);
        $sy_cost[$i][15] = round($sy_cost[$i][14] * $leasing_rate / 100, 3);
        $sy_cost[$i][16] = round($sy_cost[$i][15] / 12, 3);
    }
    
    // Итоговый расчёт ежемесячной суммы лизинга
    for($i=1;$i<=$contract_time;$i++) {
        for($j=1;$j<=12;$j++) {
            $index = ($i - 1) * 12 + $j;
            $lead_pay[$index] = array($index,0,0,0,0,0,0,0,0);
            $lead_pay[$index][1] = $u_amortiz[$i][4];
            $lead_pay[$index][2] = $Y_m;
            $lead_pay[$index][3] = $sy_cost[$i][16];
            $lead_pay[$index][4] = $additional_services_cost;
            $lead_pay[$index][5] = $lead_pay[$index][1] + $lead_pay[$index][2] + $lead_pay[$index][3] + $lead_pay[$index][4];
            $lead_pay[$index][6] = round($lead_pay[$index][5] * (1 + $nds_rate / 100), 3);
            
            if ($prepayment_cost > 0) {
                $lead_pay[$index][7] = round($prepayment_cost / ($contract_time * 12), 3);
            } else {
                $lead_pay[$index][7] = 0;
            }
            
            $lead_pay[$index][8] = $lead_pay[$index][6] - $lead_pay[$index][7];
        }
    }    
?>