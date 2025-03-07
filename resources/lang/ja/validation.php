<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attributeを承認してください。',
    'accepted_if'          => ':otherが:valueの時、:attributeを承認してください。',
    'active_url'           => ':attributeには有効なURLを指定してください。',
    'after'                => ':attributeには:date以降の日付を指定してください。',
    'after_or_equal'       => ':attributeには:date以降の日付を指定してください。',
    'alpha'                => ':attributeには英字のみからなる文字列を指定してください。',
    'alpha_dash'           => ':attributeには英数字・ハイフン・アンダースコアのみからなる文字列を指定してください。',
    'alpha_num'            => ':attributeには英数字のみを指定してください。',
    'array'                => ':attributeには配列を指定してください。',
    'ascii'                => ':attributeには半角英数字か半角記号のみを指定してください。',
    'before'               => ':attributeには:date以前の日付を指定してください。',
    'before_or_equal'      => ':attributeには:dateかそれ以前の日付を指定してください。',
    'between'              => [
        'numeric' => ':attributeには:min〜:maxまでの数値を指定してください。',
        'file'    => ':attributeには:min〜:max KBのファイルを指定してください。',
        'string'  => ':attributeには:min〜:max文字の文字列を指定してください。',
        'array'   => ':attributeには:min〜:max個の要素を持つ配列を指定してください。',
    ],
    'boolean'              => ':attributeには真偽値を指定してください。',
    'confirmed'            => ':attributeが確認用の値と一致しません。',
    'current_password'     => '正しい形式のパスワードを入力してください。',
    'date'                 => ':attributeには正しい形式の日付、又は、時間を指定してください。',
    'date_equals'          => ':attributeには:dateと同じ日付を指定してください。',
    'date_format'          => ':attributeには":format"という形式の日付を指定してください。',
    'decimal'              => ':attributeには小数部の桁数を:decimalで指定してください。',
    'declined'             => ':attributeを辞退してください。',
    'declined_if'          => ':otherが:valueの時、:attributeを辞退してください。',
    'different'            => ':attributeには:otherとは異なる値を指定してください。',
    'digits'               => ':attributeには:digits桁の数値を指定してください。',
    'digits_between'       => ':attributeには:min〜:max桁の数値を指定してください。',
    'dimensions'           => ':attributeの画像サイズが不正です。',
    'distinct'             => '指定された:attributeは既に存在しています。',
    'doesnt_end_with'      => ':attributeには次のいずれかで終わらない値を指定してください。 :values.',
    'doesnt_start_with'    => ':attributeには次のいずれかで始まらない値を指定してください。 :values.',
    'email'                => ':attributeには正しい形式のメールアドレスを指定してください。',
    'ends_with'            => ':attributeには次のいずれかで終わる値を指定してください。 :values.',
    'enum'                 => '選択された:attributeは無効です。',
    'exists'               => '指定された:attributeは存在しません。',
    'file'                 => ':attributeにはファイルを指定してください。',
    'filled'               => ':attributeには空でない値を指定してください。',
    'gt' => [
        'numeric' => ':attributeには:valueより大きい値を指定してください。',
        'file' => ':attributeには:value KBより大きいファイルを指定してください。',
        'string' => ':attributeには:valueより後の文字列を指定してください。',
        'array' => ':attributeは:valueより大きい数を選択してください。',
    ],
    'gte' => [
        'numeric' => ':attributeには:value以上の値を指定してください。',
        'file' => ':attributeには:value KB以上のファイルを指定してください。',
        'string' => ':attributeには:value以降の文字列を指定してください。',
        'array' => ':attributeは:value以上選択してください。',
    ],
    'image'                => ':attributeには画像ファイルを指定してください。',
    'in'                   => ':attributeには「:values」のうちいずれかの値を指定してください。',
    'in_array'             => ':attributeが:otherに含まれていません。',
    'integer'              => ':attributeには整数を指定してください。',
    'ip'                   => ':attributeには正しい形式のIPアドレスを指定してください。',
    'ipv4'                 => ':attributeには正しい形式のIPv4アドレスを指定してください。',
    'ipv6'                 => ':attributeには正しい形式のIPv6アドレスを指定してください。',
    'json'                 => ':attributeには正しい形式のJSON文字列を指定してください。',
    'lowercase'            => ':attributeには小文字の英文字を指定してください。',
    'lt' => [
        'numeric' => ':attributeには:valueより小さい値を指定してください。',
        'file' => ':attributeには:value KBより小さいファイルを指定してください。',
        'string' => ':attributeには:valueより前の文字列を指定してください。',
        'array' => ':attributeは:valueより小さい数を選択してください。',
    ],
    'lte' => [
        'numeric' => ':attributeには:value以下の値を指定してください。',
        'file' => ':attributeには:value KB以下のファイルを指定してください。',
        'string' => ':attributeには:value以前の文字列を指定してください。',
        'array' => ':attributeは:value以下選択してください。',
    ],
    'mac_address'          => ':attributeには有効なMACアドレスを指定してください。',
    'max'                  => [
        'numeric' => ':attributeには:max以下の数値を指定してください。',
        'file'    => ':attributeには:max KB以下のファイルを指定してください。',
        'string'  => ':attributeには:max文字以下の文字列を指定してください。',
        'array'   => ':attributeには:max個以下の要素を持つ配列を指定してください。',
    ],
    'mimes'                => ':attributeには:values形式のファイルを指定してください。',
    'mimetypes'            => ':attributeには:values形式のファイルを指定してください。',
    'min'                  => [
        'numeric' => ':attributeには:min以上の数値を指定してください。',
        'file'    => ':attributeには:min KB以上のファイルを指定してください。',
        'string'  => ':attributeには:min文字以上の文字列を指定してください。',
        'array'   => ':attributeには:min個以上の要素を持つ配列を指定してください。',
    ],
    'min_digits'           => ':attributeには:min桁以上の数値を指定してください。',
    'multiple_of'          => ':attributeには:valueの倍数を指定してください。',
    'not_in'               => ':attributeには:valuesのうちいずれとも異なる値を指定してください。',
    'not_regex'            => ':attributeの形式は無効です。',
    'numeric'              => ':attributeには数値を指定してください。',
    'password'             => [
        'letters' => ':attributeには1つ以上の英文字を含めてください。',
        'mixed' => ':attributeには大文字の英字と小文字の英字をそれぞれ1つ以上含めてください。',
        'numbers' => ':attributeには1つ以上の数字を含めてください。',
        'symbols' => ':attributeには1つ以上の記号を含めてください。',
        'uncompromised' => '指定された:attributeは、不正使用されています。. 別の:attributeを指定してください。',
    ],
    'present'              => ':attributeが存在していません。',
    'prohibited'           => ':attributeの入力は禁止されています。',
    'prohibited_if'        => ':otherが:valueの時、:attributeの入力は禁止されています。',
    'prohibited_unless'    => ':otherが:values以下の時、:attributeの入力は禁止されています。',
    'prohibits'            => ':attributeは:otherの入力を禁止します。',
    'regex'                => '正しい形式の:attributeを指定してください。',
    'required'             => ':attributeは必須です。',
    'required_array_keys'  => ':attributeは次の値を含む必要があります。:values',
    'required_if'          => ':otherが:valueの時、:attributeは必須です。',
    'required_if_accepted' => ':otherが承認されている時、:attributeは必須です。',
    'required_unless'      => ':otherが:values以外の時、:attributeは必須です。',
    'required_with'        => ':valuesのうちいずれかが指定された時、:attributeは必須です。',
    'required_with_all'    => ':valuesのうちすべてが指定された時、:attributeは必須です。',
    'required_without'     => ':valuesのうちいずれかがが指定されなかった時、:attributeは必須です。',
    'required_without_all' => ':valuesのうちすべてが指定されなかった時、:attributeは必須です。',
    'same'                 => ':attributeが:otherと一致しません。',
    'size'                 => [
        'numeric' => ':attributeには:sizeを指定してください。',
        'file'    => ':attributeには:size KBのファイルを指定してください。',
        'string'  => ':attributeには:size文字の文字列を指定してください。',
        'array'   => ':attributeには:size個の要素を持つ配列を指定してください。',
    ],
    'starts_with'          => ':attributeには次のいずれかで始まる値を指定してください。 :values.',
    'string'               => ':attributeには文字列を指定してください。',
    'timezone'             => ':attributeには正しい形式のタイムゾーンを指定してください。',
    'unique'               => 'その:attributeはすでに使われています。',
    'uploaded'             => ':attributeのアップロードに失敗しました。',
    'uppercase'            => ':attributeには大文字の英文字を指定してください。',
    'url'                  => ':attributeには正しい形式のURLを指定してください。',
    'ulid'                 => ':attributeには正しい形式のULIDを指定してください。',
    'uuid'                 => ':attributeには正しい形式のUUIDを指定してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'image_file'     => '画像',
        'image_interval' => '画像の静止時間',
        'link_url'       => 'リンクURL',
        'caption'        => 'キャプション',
        'link_target'    => 'リンクターゲット',
    ],

    /*
    |--------------------------------------------------------------------------
    | Captcha Language Lines
    |--------------------------------------------------------------------------
    */

    'captcha' => '画像上に表示されている正しい文字を入力してください。',

];
