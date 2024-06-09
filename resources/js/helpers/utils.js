export const formatOwnerInfo = (owner) => {
    let parts = []; // Array to hold parts of the display string

    // Add name if available
    if (owner?.first_name || owner?.last_name) {
        parts.push(
            `${owner?.first_name ?? ""} ${owner?.last_name ?? ""}`.trim()
        );
    }

    // Add location if both city and state are available
    if (owner?.city && owner?.state) {
        parts.push(`${owner.city}, ${owner.state}`);
    } else if (owner?.city) {
        parts.push(owner.city); // Just city or just state
    } else if (owner?.state) {
        parts.push(owner.state);
    }

    // Add FinCEN ID if available
    if (owner?.fincen_id) {
        parts.push(owner.fincen_id);
    }

    // Join all parts with ' | ' if there are multiple parts
    return parts.join(" | ");
};

// Converts and sorts countries, placing "United States" at the top
export function getCountryOptions(countries) {
    return countries
        .map((country) => ({
            value: country.id,
            label: country.name,
            territory: country.us_territory,
        }))
        .sort((a, b) => {
            if (a.label === "United States") return -1;
            if (b.label === "United States") return 1;
            return a.label.localeCompare(b.label);
        });
}

// Converts and sorts states by name
export function getStateOptions(states) {
    return states
        .map((state) => ({
            value: state.id,
            label: state.name,
        }))
        .sort((a, b) => a.label.localeCompare(b.label));
}

// Converts and sorts tribes by name
export function getTribeOptions(tribes) {
    return tribes
        .map((tribe) => ({
            value: tribe.id,
            label: tribe.name,
        }))
        .sort((a, b) => a.label.localeCompare(b.label));
}

// Finds an option by value, returns the option or a default value
export function findOption(options, value, defaultValue = {}) {
    let currentCountry = options.find((option) => option.value === value);

    return options.find((option) => option.value === value) || defaultValue;
}

// Capitalizes the first letter of each word in a string
export function capitalizeWords(str) {
    return str
        .split(" ")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
}
