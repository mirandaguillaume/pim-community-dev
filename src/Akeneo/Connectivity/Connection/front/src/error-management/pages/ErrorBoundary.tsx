import React, {Component, ReactNode} from 'react';
import {PageContent, RuntimeError} from '../../common/components';
import {NotFoundError, UnauthorizedError} from '../../shared/fetch';

class ErrorBoundary extends Component<{children?: ReactNode}, {error?: Error}> {
    constructor(props: {children?: ReactNode}) {
        super(props);
        this.state = {};
    }

    static getDerivedStateFromError(error: Error) {
        if (error instanceof UnauthorizedError) {
            // Reload the page to display the login form.
            window.location.reload();
        }

        return {error};
    }

    render() {
        if (this.state.error) {
            return (
                <PageContent>
                    {this.state.error instanceof NotFoundError ? (
                        <>
                            {/* TODO Create NotFoundError component */}
                            <RuntimeError />
                        </>
                    ) : (
                        <RuntimeError />
                    )}
                </PageContent>
            );
        }

        return this.props.children;
    }
}

export {ErrorBoundary};
