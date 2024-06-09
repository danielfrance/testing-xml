import InvitedLayout from "@/Layouts/InvitedLayout";
import { Link, useForm, usePage } from "@inertiajs/react";
import Alert from "@/Components/Alert";
import { Input, Button } from "react-daisyui";
import { useEffect } from "react";


export default function InvitedMember({ invite }) {

    const { messages } = usePage().props

    const { data, setData, post, processing, errors, reset } = useForm({
        name: invite.name,
        email: invite.email,
        password: '',
        password_confirmation: '',
        token: invite.token,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('invite.teamMember.store'));
    }

    useEffect(() => {
        return () => {
            reset('password', 'password_confirmation');
        };
    }, []);

    useEffect(() => {
    }, [errors]);

    return (
        <InvitedLayout>
            <div className="mb-5">
                {messages?.success && (

                    <Alert type="success" message={messages.success} />
                )}
                {Object.keys(errors).length > 0 && <Alert message={errors} type="error" timeOut={10000} />}
            </div>
            <h2 className="font-bold text-2xl mb-4">Accept Team Invite</h2>
            <div className="w-full">
                <form onSubmit={handleSubmit} className="min-w-96">
                    <div className="grid mb-4">
                        <label htmlFor="name" className="text-sm font-semibold text-gray-600">Team Name</label>
                        <Input
                            type="text"
                            name="team_name"
                            id="team_name"
                            value={invite.team_name}
                            className={`w-full input input-bordered ${errors.team_name ? 'input-error' : ''}`}
                            disabled
                        />
                    </div>
                    <div className="grid mb-4">
                        <label htmlFor="name" className="text-sm font-semibold text-gray-600">Name</label>
                        <Input
                            type="text"
                            name="name"
                            id="name"
                            value={data.name}
                            className={`w-full input input-bordered ${errors.name ? 'input-error' : ''}`}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                    </div>
                    <div className="grid mb-4 ">
                        <label htmlFor="email" className="text-sm font-semibold text-gray-600">Email</label>
                        <Input
                            type="text"
                            name="email"
                            id="email"
                            value={data.email}
                            className={`w-full input input-bordered ${errors.email ? 'input-error' : ''}`}
                            disabled
                        />

                    </div>
                    <div className="grid mb-4 ">
                        <label htmlFor="password" className="text-sm font-semibold text-gray-600">Password</label>
                        <Input
                            type="password"
                            name="password"
                            className={`w-full input input-bordered ${errors.password ? 'input-error' : ''}`}
                            onChange={(e) => setData('password', e.target.value)}
                        />
                    </div>
                    <div className="grid mb-4 ">
                        <label htmlFor="password" className="text-sm font-semibold text-gray-600">Confirm Password</label>
                        <Input
                            type="password"
                            name="password_confirmation"
                            className={`w-full input input-bordered ${errors.password ? 'input-error' : ''}`}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                        />
                    </div>

                    <div className="flex justify-between mt-3">
                        <Link
                            href={route('team.members.index')}
                            className="btn btn-secondary text-white font-bold">
                            Cancel
                        </Link>

                        <Button
                            type="submit"
                            className="btn btn-primary text-white font-bold"
                        >
                            {processing ? (
                                <span className="loading loading-spinner">Processing</span>
                            ) : "Create Account"}

                        </Button>
                    </div>
                </form>
            </div>

        </InvitedLayout>
    )
}