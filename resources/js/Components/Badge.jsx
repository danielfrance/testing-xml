

export default function Badge({ status, children }) {

    let type = (status === 'Pending' || status === 'pending') ? 'warning' : (status === 'Approved' || status === 'approved') ? 'success' : (status === 'Submitted' || status === 'submitted') ? 'info' : (status === 'Rejected' || status === 'rejected') ? 'error' : 'neutral';

    const className = `badge badge-${type}`;

    return (
        <div className={`${className}`}>
            {children}
        </div>
    );
}