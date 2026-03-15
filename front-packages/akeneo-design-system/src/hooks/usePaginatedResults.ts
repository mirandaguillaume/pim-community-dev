import {useBooleanState, useIsMounted} from '.';
import {DependencyList, useEffect, useRef, useState} from 'react';

const usePaginatedResults = <Type>(
  fetcher: (page: number) => Promise<Type[]>,
  dependencies: DependencyList,
  shouldFetch = true
) => {
  const [results, setResults] = useState<Type[] | null>(null);
  const [page, setPage] = useState<number>(0);
  // In React 18, state updates are batched — using state for isFetching caused infinite
  // re-fetch loops because stopFetching() and setResults() were batched into one render.
  // A ref is synchronously updated and prevents re-entrant fetching.
  const isFetchingRef = useRef(false);
  // A counter that triggers the fetch effect when dependencies change, replacing the old
  // pattern of depending on `results` (which caused re-fetch loops whenever results changed).
  const [fetchTrigger, setFetchTrigger] = useState(0);
  const [isLastPage, onLastPage, notOnLastPage] = useBooleanState();
  const isMounted = useIsMounted();

  useEffect(() => {
    if (null === results) return;

    setPage(0);
    notOnLastPage();
    isFetchingRef.current = false;
    setFetchTrigger(t => t + 1);
  }, dependencies);

  useEffect(() => {
    if (isFetchingRef.current || isLastPage || !shouldFetch) return;

    const fetchResults = async () => {
      const newResults = await fetcher(page);

      if (!isMounted()) return;

      if (newResults.length === 0) onLastPage();

      setResults(currentResults => {
        if (0 === page || null === currentResults) return newResults;

        return [...currentResults, ...newResults];
      });
      isFetchingRef.current = false;
    };

    isFetchingRef.current = true;
    void fetchResults();
  }, [page, fetchTrigger, shouldFetch]);

  useEffect(() => {
    if (shouldFetch) return;

    setPage(0);
    setResults(null);
    notOnLastPage();
  }, [shouldFetch]);

  const fetchNextPage = () => {
    if (isFetchingRef.current || isLastPage) return;

    setPage(page => page + 1);
  };

  return [results ?? [], fetchNextPage] as const;
};

export {usePaginatedResults};
