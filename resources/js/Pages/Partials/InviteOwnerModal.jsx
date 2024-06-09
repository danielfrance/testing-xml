import { useState } from "react";
import Modal from "@/Components/Modal";
import { Button, Input, Textarea } from "react-daisyui";
import { useForm } from "@inertiajs/react";


export default function InviteOwnerModal({ title, show, onClose, type }) {

    const { data, setData, post, processing, errors, reset } = useForm({
        type: type,
        name: '',
        email: '',

    })

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('invite.sendInvite'), {
            preserveScroll: true,
            onSuccess: () => {
                onClose();
                reset();
            }
        });
    }


    return (
        <Modal id="inviteOwner" show={show} maxWidth="2xl" closeable={true} onClose={onClose}>
            <div className="bg-white overflow-hidden p-6">
                <div className="flex items-center justify-between p-4 ">
                    <h3 className="font-bold text-gray-900 text-3xl mb-3">
                        {title ?? 'Invite Owner'}
                    </h3>

                </div>

                <form onSubmit={handleSubmit}>
                    <div className="component-preview p-4">
                        <div className="grid">
                            <div className="grid-row">
                                <div className="grid-col-12">
                                    <label className="label">Invitees Name:</label>
                                    <Input
                                        name="name"
                                        size="lg"
                                        className="w-full input input-bordered"

                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                    >
                                    </Input>
                                </div>
                                <div className="grid-col-12">
                                    <label className="label">Invitees Email:</label>
                                    <Input
                                        name="email"
                                        size="lg"
                                        className="w-full input input-bordered"

                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                    >
                                    </Input>
                                </div>
                                {/* <div className="grid-col-12">
                                    <label className="label">Enter a message:</label>
                                    <Textarea
                                        className="w-full textarea textarea-bordered textarea-lg"
                                        required
                                    />
                                </div> */}
                            </div>
                        </div>
                        <div className="flex justify-end mt-4">
                            <Button
                                type="submit"
                                className="btn btn-primary text-white font-bold"
                            >
                                Send Invite
                            </Button>
                        </div>
                    </div>
                </form>

            </div>
        </Modal>
    )
}