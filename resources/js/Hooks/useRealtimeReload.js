import { router } from '@inertiajs/react';
import { useEffect } from 'react';

const DEFAULT_EVENTS = [
    'QueueCreated',
    'QueueCalled',
    'QueueUpdated',
    'QueueSkipped',
    'QueueCompleted',
    'InitialCheckUpdated',
    'MedicalVisitUpdated',
    'PrescriptionUpdated',
    'PracticeSessionUpdated',
];

export default function useRealtimeReload({
    publicChannels = [],
    privateChannels = [],
    only = [],
    events = DEFAULT_EVENTS,
    pollInterval = null,
}) {
    useEffect(() => {
        const reload = () => {
            router.reload({
                only,
                preserveScroll: true,
                preserveState: true,
            });
        };

        const cleanups = [];

        if (window.Echo) {
            publicChannels.filter(Boolean).forEach((name) => {
                const channel = window.Echo.channel(name);
                events.forEach((eventName) => channel.listen(`.${eventName}`, reload));
                cleanups.push(() => window.Echo.leave(name));
            });

            privateChannels.filter(Boolean).forEach((name) => {
                const channel = window.Echo.private(name);
                events.forEach((eventName) => channel.listen(`.${eventName}`, reload));
                cleanups.push(() => window.Echo.leave(name));
            });
        }

        if (pollInterval) {
            const timer = window.setInterval(reload, pollInterval);
            cleanups.push(() => window.clearInterval(timer));
        }

        return () => {
            cleanups.forEach((cleanup) => cleanup());
        };
    }, [
        JSON.stringify(publicChannels.filter(Boolean)),
        JSON.stringify(privateChannels.filter(Boolean)),
        JSON.stringify(only),
        JSON.stringify(events),
        pollInterval,
    ]);
}
