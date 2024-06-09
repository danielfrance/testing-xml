import AuthSidebarLayout from "@/Layouts/AuthSidebarLayout";

export default function Test({auth}) { 

    return (
        <AuthSidebarLayout user={auth.user} />
    )
}