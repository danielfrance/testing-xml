import { useState, useEffect } from "react";
import { Checkbox, Input, Select, Button, Steps } from "react-daisyui"
import { PlusIcon, XMarkIcon } from "@heroicons/react/24/outline";
import SearchableSelect from "@/Components/SearchableSelect";
import TaxIDInput from "@/Components/TaxIDInput";


export default function CompanyInfoFormationInfo({ filing,
    countryOptions,
    states,
    tribes,
    taxIdTypes,
    data,
    setData,
    errors,
}) {


    const [selectedTaxIDType, setSelectedTaxIDType] = useState(data?.tax_id_type_id || '');
    const [countryTaxID, setCountryTaxID] = useState(countryOptions.find(country => {
        return country.value === data.tax_id_country_id;
    }
    ) || {});

    const [usTerritory, setUsTerritory] = useState(false);
    const [selectedState, setSelectedState] = useState(states.find(state => {
        return state.value === data.state_formation_id;
    }) || {});

    const [selectedTribe, setSelectedTribe] = useState(tribes.find(tribe => {
        return tribe.value === data.tribal_formation_id;
    }) || {});

    const [isUSAState, setIsUSAState] = useState(false);
    const [isTribal, setIsTribal] = useState(false);
    const [isUSA, setIsUSA] = useState(false);


    const [firstFormationCountry, setFirstFormationCountry] = useState(countryOptions.find(country => {
        return country.value === data.country_formation_id;
    }
    ) || {});


    const handleTaxIDTypeChange = (e) => {

        let taxIDData = {};
        taxIDData.tax_id_type_id = e;

        if (e != 3) {
            console.log('making Tax ID Type Change');
            const usa = countryOptions.find(country => {
                return country.label === 'United States';
            });
            setCountryTaxID({ value: usa.value, label: 'United States' });
            taxIDData.tax_id_country_id = usa.value;

            console.log(taxIDData);
        }


        setSelectedTaxIDType(e);

        setData({ ...data, ...taxIDData });

    };



    const taxIDCountryChange = (e) => {

        setData('tax_id_country_id', e.value);
        setCountryTaxID({ value: e.value, label: e.label });
    };

    const handleCountryFormationChange = (e) => {
        let countryData = {
            country_formation_id: e.value,
        };

        switch (true) {
            case e.territory:
                setUsTerritory(true);
                const filteredTerritory = states.filter(state => state.label === e.label);

                countryData.formation_type = 'domestic';
                countryData.state_formation_id = filteredTerritory[0].value;

                setSelectedState({
                    value: filteredTerritory[0].value,
                    label: filteredTerritory[0].label,
                });
                break;
            case e.label === 'United States':
                setIsUSA(true);
                setIsUSAState(true);
                setIsTribal(true);
                setUsTerritory(false);
                countryData.formation_type = 'domestic';
                setSelectedState((prev) => { states });

                break;
            default:
                setIsUSA(false);
                countryData.formation_type = 'foreign';
                countryData.state_formation_id = '';
                countryData.tribal_formation_id = '';
                break;
        }

        setFirstFormationCountry(e);
        console.log("updating data here;");

        setData({ ...data, ...countryData });
    }

    const handleStateFormationChange = (e) => {
        setSelectedState(e);

        if (e.value === '') {
            setIsTribal(true);
            setData('tribal_formation_id', '');

        } else {
            setIsTribal(false);
            console.log("making change here");
            setData('state_formation_id', e.value);
        }
    }


    const handleTribalFormationChange = (e) => {
        setSelectedTribe(e);

        if (e.value === '') {
            setIsUSAState(true);
            setData('tribal_formation_id', '');

        } else {
            setIsUSAState(false);
            setData('tribal_formation_id', e.value);
        }
    }

    useEffect(() => {
    }, [selectedState, countryTaxID, data, firstFormationCountry, selectedTaxIDType]);


    return (
        <>
            <div className="w-full border-b-2 mb-2">
                <h3 className="font-semibold text-lg text-gray-800 leading-tight">Tax ID Form of Identification</h3>
            </div>
            <div className="grid gap-4 grid-cols-2 mb-6">
                <label className="label">Select Tax Identification Type</label>
                <Select
                    name="tax_id_type_id"
                    className={`w-full max-w-md select select-secondary ${errors.tax_id_type_id ? 'select-error' : ''}`}
                    value={selectedTaxIDType || 'default'}
                    onChange={(e) => handleTaxIDTypeChange(e.target.value)}
                >
                    <Select.Option value={'default'} disabled>
                        Pick your Tax Identification Type
                    </Select.Option>
                    {taxIdTypes.map((type) => (
                        <Select.Option key={type.id} value={type.id}>{type.name}</Select.Option>
                    ))
                    }
                </Select>
            </div>
            <div className="grid gap-4 grid-cols-2 mb-6">
                <label className="label">Tax ID Number</label>
                <TaxIDInput
                    name="tax_id_number"
                    taxId={data.tax_id_number}
                    setTaxId={(formattedValue) => setData('tax_id_number', formattedValue)}
                    taxIdTypeId={selectedTaxIDType}
                    errors={errors.tax_id_number}
                    isDisabled={false}
                />
            </div>
            <div className="grid gap-4 grid-cols-2 mb-6">
                <label className="label">Country/Jurisdiction (if foreign tax ID only)</label>
                <SearchableSelect
                    name="tax_id_country_id"
                    disabled={selectedTaxIDType != '3'}
                    options={countryOptions}
                    placeholder="Select the country of your tax ID"
                    handleChange={(value) => taxIDCountryChange(value)}
                    value={countryTaxID}
                    error={errors.tax_id_country_id}
                />
            </div>



            <div className="w-full border-b-2 mb-2">
                <h3 className="font-semibold text-lg text-gray-800 leading-tight">Jurisdiction of formation of first registration -- where the company was first created</h3>
                {/* TODO: tooltip for this */}
            </div>
            <div className="grid gap-4 grid-cols-2 mb-6">
                <label className="label">Country/Jurisdiction of formation</label>
                <SearchableSelect
                    name="country_formation_id"
                    options={countryOptions}
                    placeholder="Select the Country of Formation"
                    handleChange={(value) => handleCountryFormationChange(value)}
                    value={firstFormationCountry}
                    error={errors.country_formation_id}
                />

            </div>

            <div className="grid gap-4 grid-cols-2 mb-6">
                <label className="label">State of Formation</label>
                <SearchableSelect
                    name="state_formation_id"
                    placeholder="Select the State of Formation"
                    handleChange={(value) => handleStateFormationChange(value)}
                    options={states}
                    disabled={!isUSA || usTerritory || !isUSAState}
                    value={selectedState}
                    error={errors.state_formation_id}
                />
            </div>
            <div className="grid gap-4 grid-cols-2 mb-6">
                <label className="label">Tribal Jurisdiction of formation</label>
                <SearchableSelect
                    name='tribal_formation_id'
                    placeholder="Select the Tribal Jurisdiction of Formation"
                    options={tribes}
                    handleChange={(value) => handleTribalFormationChange(value)}
                    value={selectedTribe}
                    disabled={usTerritory || !isUSA || !isTribal}
                    error={errors.tribal_formation_id}
                />
            </div>
            {/* TODO: if selection above is Other, then display this field */}
            <div className={`grid gap-4 grid-cols-2 mb-6 ${data.tribal_formation_id != "577" ? 'hidden' : ''}`}>
                <label className="label">Name of the other Tribe</label>
                <Input
                    name="tribal_other_name"
                    className={`max-w-md input input-bordered input-secondary ${errors.tribal_other_name ? 'input-error' : ''}`}
                    placeholder="Name of the other Tribe"
                    value={data.tribal_other_name}
                    onChange={(e) => setData('tribal_other_name', e.target.value)}
                />
            </div>
        </>
    );
}