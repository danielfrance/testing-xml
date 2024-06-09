import Modal from "@/Components/Modal";
import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";
import { PlusIcon } from "@heroicons/react/24/outline";
import { Head } from '@inertiajs/react';
import { useState } from "react";
import { Button, Table, Link } from "react-daisyui";
import { Input } from "react-daisyui";
import Badge from "@/Components/Badge";
import { capitalizeWords } from "@/helpers/utils";


// index page for reporting companies
// create modal displays when user clicks on "Add Reporting Company" button
// the user selects a filing type and is redirected to the appropriate page
// if "Initial Report" is selected, the CompanyInformation component is displayed
// otherwise, the FilingInformation component is displayed

export default function FilingIndex({ auth, filings }) {
    const [filingType, setFilingType] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');  // State to hold search term

    const setReportingFilingType = (type) => {
        setFilingType(type === filingType ? null : type);
    }

    // Handler to update the search term state as user types
    const handleSearchChange = (event) => {
        setSearchTerm(event.target.value.toLowerCase());
    }

    // Filter filings based on search term
    const filteredFilings = filings.filter(filing => {
        return (
            filing.company_info?.legal_name.toLowerCase().includes(searchTerm) ||
            filing.filing_type?.name.toLowerCase().includes(searchTerm) ||
            filing.company_info?.tax_id_number.includes(searchTerm) ||
            filing.status?.toLowerCase().includes(searchTerm)
        );
    });



    return (
        <AuthSidebarLayout
            user={auth.user}
        >
            <Head title="Filings" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg px-2 py-3">
                        <div className="flex justify-between mb-3 items-center">
                            <h2 className="font-bold text-2xl">
                                Filings
                            </h2>
                            <Button
                                tag="a"
                                href={route('filing.create')}
                                color="primary"
                                className="font-bold text-white"
                                startIcon={<PlusIcon
                                    className="h-4 w-4" />}
                            >
                                Start New Filing
                            </Button>
                        </div>
                        <div className="flex justify-between mb-3 items-center">
                            <Input placeholder="Search Filings" className="w-full" onChange={handleSearchChange} />

                        </div>
                        <div className="overflow-x-auto">
                            <Table size="lg" >
                                <Table.Head>
                                    <span>Company Name</span>
                                    <span>Filing Type</span>
                                    <span>EIN</span>
                                    <span>Status</span>
                                    <span>Actions</span>
                                </Table.Head>

                                <Table.Body>
                                    {filteredFilings.map((filing) => (
                                        <Table.Row className="hover:bg-primary/10" key={filing.id}>
                                            <span>{filing.company_info?.legal_name ?? "Draft Filing"}</span>
                                            <span>{filing.filing_type_name}</span>
                                            <span>{filing.company_info?.tax_id_number}</span>
                                            <span>
                                                <Badge status={filing.status}>{capitalizeWords(filing.status)}</Badge>
                                            </span>
                                            <span>
                                                <Link href={route('filing.show', filing.id)} className="text-primary">Edit</Link>
                                            </span>
                                        </Table.Row>
                                    ))}
                                </Table.Body>
                            </Table>
                        </div>
                    </div>
                </div>
            </div>

        </AuthSidebarLayout>
    );
}
