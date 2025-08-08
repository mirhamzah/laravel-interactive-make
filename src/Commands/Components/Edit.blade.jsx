import React, { useState, useEffect } from 'react';
import axios from 'axios';

const Edit = ({ modelId }) => {

    // Initialize form state from fields
    const [form, setForm] = useState({
@foreach ($fields as $field)
        {{$field['name']}}: '{{$field['value'] ?? null}}',
@endforeach
    });
        
    const [loading, setLoading] = useState(true);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        setLoading(false);
        /*
        axios.get(`/api/model/${modelId}`)
            .then(res => {
                setForm(res.data);
                setLoading(false);
            })
            .catch(() => setLoading(false));
        */
    }, [modelId]);

    const handleChange = e => {
        setForm({ ...form, [e.target.name]: e.target.value });
    };

    const handleSubmit = e => {
        e.preventDefault();
        /*
        axios.put(`/api/model/${modelId}`, form)
            .then(() => alert('Updated successfully!'))
            .catch(err => {
                if (err.response && err.response.data.errors) {
                    setErrors(err.response.data.errors);
                }
            });
        */
    };

    if (loading) return <div>Loading...</div>;

    return (
        <form onSubmit={handleSubmit}>

@foreach ($fields as $field)
            <div key="{{$field['name']}}">
                <label htmlFor="{{$field['name']}}">{{$field['label']}}:</label>
                <input
                    type="{{$field['type']}}"
                    name="{{$field['name']}}"
                    value={form.{{$field['name']}}}
                    onChange={handleChange}
                />
                {errors.{{$field['name']}} && <div className='error'>{errors.name[0]}</div>}
            </div>
@endforeach
            <button type="submit">Update</button>
        </form>
    );
};

export default Edit;