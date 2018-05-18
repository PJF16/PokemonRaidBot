<?php
// Write to log.
debug_log('POKEDEX()');

// For debug.
//debug_log($update);
//debug_log($data);

// Check access - user must be admin!
bot_access_check($update, BOT_ADMINS);

$rs = my_query(
            "
            SELECT   r.gym_name,
                    SUM(CASE 
                         WHEN a.cancel=FALSE or a.raid_done=FALSE 
                         THEN (a.extra_mystic+a.extra_valor+a.extra_instinct+1)
                         ELSE 0 
                        END) as Total_attended,
                    count(distinct r.id) as Total_raids,    
  ROUND((SUM(CASE WHEN a.cancel=FALSE or a.raid_done=FALSE THEN (a.extra_mystic+a.extra_valor+a.extra_instinct+1)
            ELSE 0 END)
        /
        count(distinct r.id)*2) +3)  as players_needed_to_trigger
FROM raids r
		LEFT JOIN pokemon ON pokemon.pokedex_id=r.pokemon
		LEFT JOIN attendance a ON a.raid_id=r.id
	WHERE   r.gym_name like '%!%'
	AND pokemon.pokedex_id <> 150
    AND WEEK(r.start_time)  BETWEEN week(now())-2 AND week(now())
	GROUP BY  r.gym_name
            "
        );

    // Init empty keys array.
    $keys = array();
        $keys[0]['text'] = 'Gym Name';
        $keys[0]['callback_data'] = 0;
        $keys[1]['text'] = 'Total Raided';
        $keys[1]['callback_data'] = 0;
        $keys[2]['text'] = 'Total Raids';
        $keys[2]['callback_data'] = 0;
        $keys[3]['text'] = 'Players Needed to Trigger';
        $keys[3]['callback_data'] = 0;
    $i = 4;
    while ($gym = $rs->fetch_assoc()) {
        $keys[$i]['text'] = $gym['gym_name'];
        $keys[$i]['callback_data'] = 0;
        
        $keys[$i+1]['text'] = $gym['Total_attended'];
        $keys[$i+1]['callback_data'] = 0;
        
        $keys[$i+2]['text'] = $gym['Total_raids'];
        $keys[$i+2]['callback_data'] = 0;
        
        $keys[$i+3]['text'] = $gym['players_needed_to_trigger'];
        $keys[$i+3]['callback_data'] = 0;
        $i = $i+4;
    }
    // Get the inline key array.
    $keys = inline_key_array($keys, 4);





// Set message.
$msg = '<b>' . getTranslation('pokedex_start') . ':</b>';

// Send message.
send_message($update['message']['chat']['id'], $msg, $keys, ['reply_markup' => ['selective' => true, 'one_time_keyboard' => true]]);

exit();