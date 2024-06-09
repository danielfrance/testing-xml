import { useState, useEffect } from 'react';
import Select, { components } from 'react-select'

export default function SearchableSelect({ name, options, handleChange, placeholder, disabled, value, error }) {


    const [localValue, setLocalValue] = useState(value);

    const customStyles = {
        control: (provided, state) => ({
            ...provided,
            minHeight: '3rem',
            width: '100%',
            maxWidth: '28rem', // Matches max-w-md
            paddingLeft: '1rem',
            paddingRight: '2.5rem',
            lineHeight: '2',
            borderRadius: '0rem', // corresponds to --rounded-btn
            borderWidth: '1px',

            boxShadow: 'none', // Optional: Remove shadow on focus if any
            '&:hover': {
                borderColor: '#3B82F6' // Tailwind blue-500 on hover
            },
            appearance: 'none',
            display: 'flex',
            alignItems: 'center',
            userSelect: 'none',
            borderColor: error
                ? "#FF0000"
                : state.isDisabled
                    ? '#EFEFEF'
                    : state.isFocused
                        ? '#3B82F6'
                        : '#6d6e70',
            backgroundPosition: 'calc(100% - 20px) calc(1px + 50%), calc(100% - 16.1px) calc(1px + 50%)',
            backgroundSize: '4px 4px, 4px 4px',
            backgroundRepeat: 'no-repeat',
            backgroundImage: state.isDisabled ? '#EFEFEF' : 'linear-gradient(45deg, transparent 50%, currentColor 50%), linear-gradient(135deg, currentColor 50%, transparent 50%)',
            backgroundColor: state.isDisabled ? '#EFEFEF' : 'white',
            cursor: state.isDisabled ? 'not-allowed' : 'pointer',
            color: state.isDisabled ? '#EFEFEF' : '#2e2e2e',
        }),
        valueContainer: (provided) => ({
            ...provided,
            padding: '0 12px',
            border: 'none',
        }),
        input: (provided, state) => ({
            ...provided,
            margin: '0px',
            outline: 'none', // This will remove the outline on focus
            boxShadow: 'none',

        }),
        indicatorSeparator: (state) => ({
            display: 'none',
            border: 'none',
        }),
        dropdownIndicator: (provided) => ({
            display: 'none', // Hides the dropdown indicator
        }),

        container: (provided) => ({
            ...provided,
            border: 'none',
        }),
        option: (provided, state) => ({
            ...provided,
            border: 'none',
            backgroundColor: state.isSelected ? '#E5E7EB' : state.isFocused ? '#EFF6FF' : 'white',
            color: state.isSelected ? '#1F2937' : '#1F2937',
            cursor: 'pointer',
            '&:active': {
                backgroundColor: '#E5E7EB'
            },
        }),
    };

    const handleInputChange = (option) => {
        // console.log(option);
        handleChange(option);  // Pass the selected option directly
        setLocalValue(option);  // Set the local value to the selected option
    };


    useEffect(() => {
        if (value && value.value !== undefined && value.label !== undefined) {
            setLocalValue({
                value: value.value,
                label: value.label
            });
        } else {
            setLocalValue(null);  // Explicitly set to null if the conditions are not met
        }
    }, [value]);


    return (
        <Select
            name={name}
            className="searchableSelect"
            // options={options}
            // add a blank option to the beginning of the options array
            options={[{ value: '', label: '' }, ...options]}
            styles={customStyles}
            onChange={handleInputChange}
            placeholder={placeholder}
            classNamePrefix="Select"
            isDisabled={disabled}
            value={localValue}
        />


    );
}
