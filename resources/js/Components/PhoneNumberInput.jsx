import { useState } from "react";
import PhoneInput from "react-phone-number-input";
import 'react-phone-number-input/style.css'


export default function PhoneNumberInput({ label, name, value, setPhoneNumber, onChange, error }) {

    const [phoneValue, setPhoneValue] = useState(value);

    const handlePhoneChange = (value) => {
        setPhoneNumber(value);
        setPhoneValue(value);
    }

    return (
        <PhoneInput
            international
            defaultCountry="US"
            className="max-w-md input input-bordered input-secondary"
            placeholder={label}
            value={phoneValue}
            onChange={(value) => handlePhoneChange(value)}
            name={name}
        />
    )

}