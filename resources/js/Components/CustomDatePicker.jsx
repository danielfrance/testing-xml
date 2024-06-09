import { useState } from 'react';
import DatePicker from "react-datepicker";

import "react-datepicker/dist/react-datepicker.css";

export default function CustomDatePicker ({ selectedDate, onChange }) {
    const [startDate, setStartDate] = useState(selectedDate);

    const handleDateChange = (date) => {
        setStartDate(date);
        onChange(date); // Propagate the selected date to the parent component
    };

    return (
        <DatePicker
            showIcon
            selected={startDate}
            onChange={(date) => setStartDate(date)}
        />
    );
};


