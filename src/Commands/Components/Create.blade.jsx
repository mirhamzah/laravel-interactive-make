import React, { useState } from 'react';

const Create = () => {

    // Initialize form state from fields
    const [form, setForm] = useState({
@foreach ($fields as $field)
        {{$field['name']}}: '{{$field['value'] ?? null}}',
@endforeach
    });

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value,
        });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        // Handle form submission logic here
        console.log(form);
    };

    return (
        <form onSubmit={handleSubmit}>
            <h1>Create {{ $model }}</h1>
@foreach ($fields as $field)
            <div key="{{$field['name']}}">
                <label htmlFor="{{$field['name']}}">{{$field['label']}}:</label>
                <input
                    type="{{$field['type']}}"
                    name="{{$field['name']}}"
                    value={form.{{$field['name']}}}
                    onChange={handleChange}
                />
            </div>
@endforeach
            <button type="submit">Submit</button>
        </form>
    );
};

export default Create;