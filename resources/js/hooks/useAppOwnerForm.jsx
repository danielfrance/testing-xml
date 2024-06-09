import { useState, useEffect, useMemo } from 'react';
import { findOption, getCountryOptions, getStateOptions, getTribeOptions } from '@/helpers/utils'

export const useAppOwnerForm = (initialData, countries, states, tribes) => {
    const countryOptions = useMemo(() => getCountryOptions(countries), [countries]);
    const stateOptions = useMemo(() => getStateOptions(states), [states]);
    const tribeOptions = useMemo(() => getTribeOptions(tribes), [tribes]);

    const [currentCountry, setCurrentCountry] = useState(findOption(countryOptions, initialData.country_id));
    const [currentState, setCurrentState] = useState(findOption(stateOptions, initialData.state_id));
    const [idDocumentCountry, setIdDocumentCountry] = useState(findOption(countryOptions, initialData.id_document_country));
    const [idDocumentState, setIdDocumentState] = useState(findOption(stateOptions, initialData.id_document_state));
    const [idDocumentTribe, setIdDocumentTribe] = useState(findOption(tribeOptions, initialData.id_document_tribe));
    const [IDType, setIDType] = useState(initialData.id_type);
    const [showTribeOtherName, setShowTribeOtherName] = useState(false);
    const [isFinCendValid, setIsFinCendValid] = useState(false);
    const [validation, setValidation] = useState({
        fincen_id: '',
    });


    useEffect(() => {
        setCurrentCountry(findOption(countryOptions, initialData.country_id));
        setCurrentState(findOption(stateOptions, initialData.state_id));
        setIdDocumentCountry(findOption(countryOptions, initialData.id_document_country));
        setIdDocumentState(findOption(stateOptions, initialData.id_document_state));
        setIdDocumentTribe(findOption(tribeOptions, initialData.id_document_tribe));
        setIDType(initialData.id_type);

    }, [initialData, countryOptions, stateOptions, tribeOptions]);

    const handleCurrentCountryChange = (selectedOption) => {
        setCurrentCountry(selectedOption);
        const newData = { country_id: selectedOption.value };

        if (selectedOption.territory) {
            const filteredTerritory = stateOptions.filter(state => state.label === selectedOption.label);
            setCurrentState(filteredTerritory[0]);
            newData.state_id = filteredTerritory[0].value;
        }
        if (selectedOption.label !== 'United States' && !selectedOption.territory) {
            setCurrentState({});
            newData.state_id = '';
        }

        // Replace setData with appropriate data update logic
        // setData({ ...data, ...newData });
        return newData;  // Return new data to be handled by the component using this hook
    };

    const isFincenIdValid = (e) => {
        // Check if the FinCEN ID has exactly 12 numeral characters and meets other conditions
        // let check = /^\d{12}$/.test(e) && e[0] !== '2';

        // if (e.length === 0) {
        //     setValidation({ ...validation, fincen_id: '' });
        // } else if (!check) {
        //     setValidation({ ...validation, fincen_id: 'Invalid FinCEN ID. Can only be 12 numbers with no letters, spaces, or special characters.' });
        // } else {
        //     setValidation({ ...validation, fincen_id: '' });
        // }

        // setIsFinCendValid(check);
        // return { fincen_id: e };
        let check = /^\d{12}$/.test(e) && e[0] !== '2';

        if (e.length === 0) {
            setValidation({ ...validation, fincen_id: '' });
            setIsFinCendValid(false);
        } else if (!check) {
            setValidation({ ...validation, fincen_id: 'Invalid FinCEN ID. Can only be 12 numbers with no letters, spaces, or special characters.' });
            setIsFinCendValid(false);
        } else {
            setValidation({ ...validation, fincen_id: '' });
            setIsFinCendValid(true);
        }
    };

    const handleCurrentStateChange = (e) => {
        setCurrentState(e);
    }

    const handleIDTypeChange = (e) => {

        let newData = {
            id_type: e,
        };

        switch (true) {
            case e === 'us_passport':
                setIdDocumentCountry({
                    value: 235,
                    label: 'United States',
                    territory: false
                });
                setIdDocumentState({});
                setIdDocumentTribe({});
                newData.id_document_country = 235;
                newData.id_document_state = '';
                newData.id_document_tribe = '';
                break;
            case e === 'foreign_passport':
                setIdDocumentCountry({});
                setIdDocumentState({});
                setIdDocumentTribe({});
                newData.id_document_country = '';
                newData.id_document_state = '';
                newData.id_document_tribe = '';
                break;
            default:
                setIdDocumentCountry({
                    value: 235,
                    label: 'United States',
                    territory: false
                });
                setIdDocumentState({});
                setIdDocumentTribe({});
                newData.id_document_country = 235;
                newData.id_document_state = '';
                newData.id_document_tribe = '';
                break;
        }

        setIDType(e);
        return newData;
    }

    const handleIDCountryChange = (e) => {
        let newData = {
            id_document_country: e.value,
        }

        setIdDocumentCountry(e);

        if (e.territory) {
            const filteredTerritory = stateOptions.filter(state => state.label === e.label);

            newData.id_document_state = filteredTerritory[0].value;

            setIdDocumentState({
                value: filteredTerritory[0].value,
                label: filteredTerritory[0].label,
            });
        }
        if (e.label !== 'United States' && !e.territory) {
            setIdDocumentState({});
        }

        return newData;

    }

    const handleIDStateChange = (e) => {


        let newData = {};
        if (e.value !== '') {
            setIdDocumentState(e);
            setIdDocumentTribe({});

            newData.id_document_state = e.value;
            newData.id_document_tribe = '';
        } else {
            setIdDocumentTribe(e);
            newData.id_document_tribe = '';
            newData.id_document_state = '';
        }

        return newData;
    }

    const handleIDTribeChange = (e) => {
        // if tribe is selected, id_document_state must be '' and disabled
        let newData = {};
        if (e.value !== '') {
            setIdDocumentState({});
            setIdDocumentTribe(e);

            newData.id_document_state = '';
            newData.id_document_tribe = e.value;

            // if tribe name is Other, show input field
            if (e.value === 577) {
                setShowTribeOtherName(true);
            } else {
                setShowTribeOtherName(false);
            }
        } else {
            setIdDocumentTribe(e);
            newData.id_document_tribe = '';
            newData.id_document_state = '';
        }

        return newData;
    }



    return {
        countryOptions,
        stateOptions,
        tribeOptions,
        currentCountry,
        currentState,
        idDocumentCountry,
        idDocumentState,
        idDocumentTribe,
        IDType,
        showTribeOtherName,
        isFinCendValid,
        validation,
        handleCurrentCountryChange,
        handleCurrentStateChange,
        handleIDTypeChange,
        handleIDCountryChange,
        handleIDStateChange,
        handleIDTribeChange,
        isFincenIdValid,
        // Other handlers...
    };
};
