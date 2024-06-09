import { Link, useForm } from "@inertiajs/react";
import { useEffect, useState } from "react";
import { Button, Input, Select } from "react-daisyui";

export default function MemberForm({ member, roles }) {

    const { data, setData, post, processing, errors, reset } = useForm({
        name: member.name,
        email: member.email,
        role: member.role,
        role_id: member.roles[0].id
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        // console.log(data);
        post(route('team.members.update', member.id));
    }

    // as long as there is memberData, render the component
    return (
        <form onSubmit={handleSubmit}>
            <div className="flex flex-col">
                <label htmlFor="name" className="text-sm font-semibold text-gray-600">Name</label>
                <Input
                    type="text"
                    name="name"
                    id="name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    className={`input input-bordered ${errors.name ? 'input-error' : 'input-secondary'}`}
                />
            </div>
            <div className="flex flex-col mt-3">
                <label htmlFor="email" className="text-sm font-semibold text-gray-600">Email</label>
                <Input
                    type="email"
                    name="email"
                    id="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    className={`input input-bordered ${errors.email ? 'input-error' : 'input-secondary'}`} />
            </div>
            <div className="flex flex-col mt-3">
                <label htmlFor="role" className="text-sm font-semibold text-gray-600">Role</label>
                {roles && (
                    <Select
                        name="role"
                        id="role"
                        className={`select select-secondary  ${errors.role ? 'select-error' : ''}`}
                        defaultValue={data.role_id}
                        onChange={(e) => setData('role_id', e.target.value)}
                    >
                        {roles.map(role => (
                            <option
                                key={role.id}
                                value={role.id}
                            >
                                {role.name}
                            </option>

                        ))}
                    </Select>
                )}
            </div>

            <div className="flex justify-between mt-3">
                <Link
                    href={route('team.members.index')}
                    className="btn btn-secondary text-white font-bold">
                    Cancel
                </Link>
                <Link
                    as="a"
                    href={route('team.members.passwordReset', member.id)}
                    className="btn btn-accent text-white font-bold">
                    Send Password Reset
                </Link>
                <Button
                    type="submit"
                    className="btn btn-primary text-white font-bold"
                >
                    {processing ? (
                        <span className="loading loading-spinner">Processing</span>
                    ) : "Update"}

                </Button>
            </div>
        </form>
    )
}