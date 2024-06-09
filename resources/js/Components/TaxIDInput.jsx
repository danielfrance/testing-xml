
import { useEffect, useState } from "react";
import { Input } from "react-daisyui"


const TaxIDInput = ({ name, taxId, setTaxId, taxIdTypeId, errors, isDisabled, classes }) => {

    useEffect(() => {
        handleFormatTaxId(taxId);
    }, [taxIdTypeId]);

    const handleFormatTaxId = (value) => {
        let formatted;
        if (taxIdTypeId == 1) {
            formatted = value.replace(/[^0-9]/g, '').slice(0, 10);
            if (formatted.length > 2) {
                formatted = `${formatted.slice(0, 2)}-${formatted.slice(2)}`;
            }
        } else if (taxIdTypeId == 2) {
            formatted = value.replace(/[^0-9]/g, '').slice(0, 10);
            formatted = formatted.replace(/(\d{3})(\d{2})?(\d{1,4})?/, '$1-$2-$3').replace(/-$/g, '');
        } else {
            formatted = value.replace(/[^0-9]/g, '').slice(0, 19);
        }

        setTaxId(formatted);
    };


    return (
        <Input
            name={name}
            className={`max-w-md input input-bordered input-secondary ${errors ? 'input-error' : ''}`}
            placeholder="Tax ID Number"
            disabled={isDisabled}
            value={taxId}
            onChange={(e) => handleFormatTaxId(e.target.value)}
        />
    );
};

export default TaxIDInput;