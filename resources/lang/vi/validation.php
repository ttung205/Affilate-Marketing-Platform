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

    'accepted' => 'Trường :attribute phải được chấp nhận.',
    'accepted_if' => 'Trường :attribute phải được chấp nhận khi :other bằng :value.',
    'active_url' => 'Trường :attribute không phải là một URL hợp lệ.',
    'after' => 'Trường :attribute phải là một ngày sau ngày :date.',
    'after_or_equal' => 'Trường :attribute phải là một ngày sau hoặc bằng ngày :date.',
    'alpha' => 'Trường :attribute chỉ có thể chứa các chữ cái.',
    'alpha_dash' => 'Trường :attribute chỉ có thể chứa chữ cái, số và dấu gạch ngang.',
    'alpha_num' => 'Trường :attribute chỉ có thể chứa chữ cái và số.',
    'array' => 'Trường :attribute phải là một mảng.',
    'ascii' => 'Trường :attribute chỉ có thể chứa các ký tự ASCII một byte.',
    'before' => 'Trường :attribute phải là một ngày trước ngày :date.',
    'before_or_equal' => 'Trường :attribute phải là một ngày trước hoặc bằng ngày :date.',
    'between' => [
        'array' => 'Trường :attribute phải có từ :min đến :max phần tử.',
        'file' => 'Trường :attribute phải có dung lượng từ :min đến :max KB.',
        'numeric' => 'Trường :attribute phải có giá trị từ :min đến :max.',
        'string' => 'Trường :attribute phải có độ dài từ :min đến :max ký tự.',
    ],
    'boolean' => 'Trường :attribute phải có giá trị true hoặc false.',
    'can' => 'Trường :attribute chứa một giá trị không được phép.',
    'confirmed' => 'Xác nhận :attribute không khớp.',
    'current_password' => 'Mật khẩu không đúng.',
    'date' => 'Trường :attribute không phải là một ngày hợp lệ.',
    'date_equals' => 'Trường :attribute phải bằng ngày :date.',
    'date_format' => 'Trường :attribute không khớp với định dạng :format.',
    'decimal' => 'Trường :attribute phải có :decimal chữ số thập phân.',
    'declined' => 'Trường :attribute phải được từ chối.',
    'declined_if' => 'Trường :attribute phải được từ chối khi :other bằng :value.',
    'different' => 'Trường :attribute và :other phải khác nhau.',
    'digits' => 'Trường :attribute phải có :digits chữ số.',
    'digits_between' => 'Trường :attribute phải có từ :min đến :max chữ số.',
    'dimensions' => 'Trường :attribute có kích thước hình ảnh không hợp lệ.',
    'distinct' => 'Trường :attribute có giá trị trùng lặp.',
    'doesnt_end_with' => 'Trường :attribute không được kết thúc bằng một trong các giá trị sau: :values.',
    'doesnt_start_with' => 'Trường :attribute không được bắt đầu bằng một trong các giá trị sau: :values.',
    'email' => 'Trường :attribute phải là một địa chỉ email hợp lệ.',
    'ends_with' => 'Trường :attribute phải kết thúc bằng một trong các giá trị sau: :values.',
    'enum' => 'Giá trị đã chọn cho :attribute không hợp lệ.',
    'exists' => 'Giá trị đã chọn cho :attribute không hợp lệ.',
    'extensions' => 'Trường :attribute phải có một trong các phần mở rộng sau: :values.',
    'file' => 'Trường :attribute phải là một tệp.',
    'filled' => 'Trường :attribute phải có giá trị.',
    'gt' => [
        'array' => 'Trường :attribute phải có nhiều hơn :value phần tử.',
        'file' => 'Trường :attribute phải có dung lượng lớn hơn :value KB.',
        'numeric' => 'Trường :attribute phải có giá trị lớn hơn :value.',
        'string' => 'Trường :attribute phải có độ dài lớn hơn :value ký tự.',
    ],
    'gte' => [
        'array' => 'Trường :attribute phải có ít nhất :value phần tử.',
        'file' => 'Trường :attribute phải có dung lượng lớn hơn hoặc bằng :value KB.',
        'numeric' => 'Trường :attribute phải có giá trị lớn hơn hoặc bằng :value.',
        'string' => 'Trường :attribute phải có độ dài lớn hơn hoặc bằng :value ký tự.',
    ],
    'hex_color' => 'Trường :attribute phải là một mã màu hex hợp lệ.',
    'image' => 'Trường :attribute phải là một hình ảnh.',
    'in' => 'Giá trị đã chọn cho :attribute không hợp lệ.',
    'in_array' => 'Trường :attribute không tồn tại trong :other.',
    'integer' => 'Trường :attribute phải là một số nguyên.',
    'ip' => 'Trường :attribute phải là một địa chỉ IP hợp lệ.',
    'ipv4' => 'Trường :attribute phải là một địa chỉ IPv4 hợp lệ.',
    'ipv6' => 'Trường :attribute phải là một địa chỉ IPv6 hợp lệ.',
    'json' => 'Trường :attribute phải là một chuỗi JSON hợp lệ.',
    'lowercase' => 'Trường :attribute phải là chữ thường.',
    'lt' => [
        'array' => 'Trường :attribute phải có ít hơn :value phần tử.',
        'file' => 'Trường :attribute phải có dung lượng nhỏ hơn :value KB.',
        'numeric' => 'Trường :attribute phải có giá trị nhỏ hơn :value.',
        'string' => 'Trường :attribute phải có độ dài nhỏ hơn :value ký tự.',
    ],
    'lte' => [
        'array' => 'Trường :attribute phải có không quá :value phần tử.',
        'file' => 'Trường :attribute phải có dung lượng nhỏ hơn hoặc bằng :value KB.',
        'numeric' => 'Trường :attribute phải có giá trị nhỏ hơn hoặc bằng :value.',
        'string' => 'Trường :attribute phải có độ dài nhỏ hơn hoặc bằng :value ký tự.',
    ],
    'mac_address' => 'Trường :attribute phải là một địa chỉ MAC hợp lệ.',
    'max' => [
        'array' => 'Trường :attribute không được có nhiều hơn :max phần tử.',
        'file' => 'Trường :attribute không được có dung lượng lớn hơn :max KB.',
        'numeric' => 'Trường :attribute không được lớn hơn :max.',
        'string' => 'Trường :attribute không được dài hơn :max ký tự.',
    ],
    'max_digits' => 'Trường :attribute không được có nhiều hơn :max chữ số.',
    'mimes' => 'Trường :attribute phải là một tệp có định dạng: :values.',
    'mimetypes' => 'Trường :attribute phải là một tệp có định dạng: :values.',
    'min' => [
        'array' => 'Trường :attribute phải có ít nhất :min phần tử.',
        'file' => 'Trường :attribute phải có dung lượng ít nhất :min KB.',
        'numeric' => 'Trường :attribute phải có giá trị ít nhất :min.',
        'string' => 'Trường :attribute phải có độ dài ít nhất :min ký tự.',
    ],
    'min_digits' => 'Trường :attribute phải có ít nhất :min chữ số.',
    'missing' => 'Trường :attribute phải thiếu.',
    'missing_if' => 'Trường :attribute phải thiếu khi :other bằng :value.',
    'missing_unless' => 'Trường :attribute phải thiếu trừ khi :other bằng :value.',
    'missing_with' => 'Trường :attribute phải thiếu khi :values có giá trị.',
    'missing_with_all' => 'Trường :attribute phải thiếu khi :values có giá trị.',
    'multiple_of' => 'Trường :attribute phải là bội số của :value.',
    'not_in' => 'Giá trị đã chọn cho :attribute không hợp lệ.',
    'not_regex' => 'Định dạng :attribute không hợp lệ.',
    'numeric' => 'Trường :attribute phải là một số.',
    'password' => [
        'letters' => 'Trường :attribute phải chứa ít nhất một chữ cái.',
        'mixed' => 'Trường :attribute phải chứa ít nhất một chữ hoa và một chữ thường.',
        'numbers' => 'Trường :attribute phải chứa ít nhất một số.',
        'symbols' => 'Trường :attribute phải chứa ít nhất một ký tự đặc biệt.',
        'uncompromised' => 'Trường :attribute đã xuất hiện trong một vụ rò rỉ dữ liệu. Vui lòng chọn một :attribute khác.',
    ],
    'present' => 'Trường :attribute phải có mặt.',
    'present_if' => 'Trường :attribute phải có mặt khi :other bằng :value.',
    'present_unless' => 'Trường :attribute phải có mặt trừ khi :other bằng :value.',
    'present_with' => 'Trường :attribute phải có mặt khi :values có giá trị.',
    'present_with_all' => 'Trường :attribute phải có mặt khi :values có giá trị.',
    'prohibited' => 'Trường :attribute bị cấm.',
    'prohibited_if' => 'Trường :attribute bị cấm khi :other bằng :value.',
    'prohibited_unless' => 'Trường :attribute bị cấm trừ khi :other bằng :value.',
    'prohibits' => 'Trường :attribute cấm :other có mặt.',
    'regex' => 'Định dạng :attribute không hợp lệ.',
    'required' => 'Trường :attribute là bắt buộc.',
    'required_array_keys' => 'Trường :attribute phải chứa các mục nhập cho: :values.',
    'required_if' => 'Trường :attribute là bắt buộc khi :other bằng :value.',
    'required_if_accepted' => 'Trường :attribute là bắt buộc khi :other được chấp nhận.',
    'required_unless' => 'Trường :attribute là bắt buộc trừ khi :other nằm trong :values.',
    'required_with' => 'Trường :attribute là bắt buộc khi :values có giá trị.',
    'required_with_all' => 'Trường :attribute là bắt buộc khi :values có giá trị.',
    'required_without' => 'Trường :attribute là bắt buộc khi :values không có giá trị.',
    'required_without_all' => 'Trường :attribute là bắt buộc khi không có :values nào có giá trị.',
    'same' => 'Trường :attribute và :other phải khớp nhau.',
    'size' => [
        'array' => 'Trường :attribute phải chứa :size phần tử.',
        'file' => 'Trường :attribute phải có dung lượng :size KB.',
        'numeric' => 'Trường :attribute phải có giá trị :size.',
        'string' => 'Trường :attribute phải có độ dài :size ký tự.',
    ],
    'starts_with' => 'Trường :attribute phải bắt đầu bằng một trong các giá trị sau: :values.',
    'string' => 'Trường :attribute phải là một chuỗi.',
    'timezone' => 'Trường :attribute phải là một múi giờ hợp lệ.',
    'unique' => 'Trường :attribute đã được sử dụng.',
    'uploaded' => 'Trường :attribute tải lên thất bại.',
    'uppercase' => 'Trường :attribute phải là chữ hoa.',
    'url' => 'Trường :attribute không phải là một URL hợp lệ.',
    'ulid' => 'Trường :attribute phải là một ULID hợp lệ.',
    'uuid' => 'Trường :attribute phải là một UUID hợp lệ.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
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
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'họ và tên',
        'email' => 'email',
        'password' => 'mật khẩu',
        'password_confirmation' => 'xác nhận mật khẩu',
        'role' => 'vai trò',
        'terms' => 'điều khoản sử dụng',
        'google_id' => 'ID Google',
        'avatar' => 'ảnh đại diện',
    ],

];
