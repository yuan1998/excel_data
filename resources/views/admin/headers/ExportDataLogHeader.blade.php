<div>
    @foreach($data as $key => $value)
        <div>
            有 {{$value}} 个 {{ data_get($queueName , $key , '数据')  }} 正在查询中...
        </div>
    @endforeach
</div>
