export default function ReviewApplicants({ data }) {
    const formatLabel = (key) => {
        return key.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
    };

    return (
        <div className="border-2 border-sky-100 mb-3 bg-slate-50 p-1">
            <h2 className="font-semibold text-2xl text-gray-800 leading-tight">{`${data.first_name} ${data.last_name} `}</h2>
            {Object.entries(data).map(([key, value], index) => {
                // console.log(key, value);
                // Skip pivot table or other nested objects/arrays
                if (typeof value === 'object' && value !== null ||
                    key === 'team_id' ||
                    key === 'id' ||
                    key === 'created_at' ||
                    key === "updated_at" ||
                    key === 'deleted_at') {
                    return null;
                }

                return (
                    <div key={index} className="grid gap-4 grid-cols-2 mb-6 border border-t-0 border-l-0 border-r-0 border-b-1">
                        <label className="label">{formatLabel(key)}</label>
                        <p className="max-w-md font-semibold">
                            {typeof value === 'boolean' ? (value ? 'Yes' : 'No') : value}
                        </p>
                    </div>
                );
            })}
        </div>
    );
}